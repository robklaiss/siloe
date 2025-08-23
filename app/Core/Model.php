<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Base Model class that provides common database operations
 */
abstract class Model {
    /**
     * The database table name
     */
    protected static $table = '';
    
    /**
     * The primary key for the model
     */
    protected static $primaryKey = 'id';
    
    /**
     * The database connection
     */
    protected static $db;
    
    /**
     * The model's attributes
     */
    protected $attributes = [];
    
    /**
     * Whether the model exists in the database
     */
    protected $exists = false;
    
    /**
     * The model's fillable attributes
     */
    protected $fillable = [];
    
    /**
     * The model's hidden attributes
     */
    protected $hidden = [];
    
    /**
     * The model's timestamps
     */
    public $timestamps = true;
    
    /**
     * The name of the "created at" column
     */
    const CREATED_AT = 'created_at';
    
    /**
     * The name of the "updated at" column
     */
    const UPDATED_AT = 'updated_at';
    
    /**
     * Create a new model instance
     */
    public function __construct(array $attributes = []) {
        $this->fill($attributes);
        
        // Set the database connection
        if (!self::$db) {
            self::$db = getDbConnection();
        }
    }
    
    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Check if the given attribute is fillable
     */
    protected function isFillable($key) {
        return in_array($key, $this->fillable);
    }
    
    /**
     * Set a given attribute on the model
     */
    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Get an attribute from the model
     */
    public function getAttribute($key) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return null;
    }
    
    /**
     * Dynamically retrieve attributes on the model
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }
    
    /**
     * Dynamically set attributes on the model
     */
    public function __set($key, $value) {
        return $this->setAttribute($key, $value);
    }
    
    /**
     * Check if an attribute exists on the model
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Get the table associated with the model
     */
    public static function getTableName() {
        return static::$table ?: strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
    }
    
    /**
     * Get all records from the database
     */
    public static function all() {
        $table = static::getTableName();
        $stmt = self::getDb()->query("SELECT * FROM {$table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }
    
    /**
     * Find a model by its primary key
     */
    public static function find($id) {
        $table = static::getTableName();
        $primaryKey = static::$primaryKey;
        
        $stmt = self::getDb()->prepare("SELECT * FROM {$table} WHERE {$primaryKey} = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        
        $result = $stmt->fetchObject(static::class);
        
        if ($result) {
            $result->exists = true;
        }
        
        return $result ?: null;
    }
    
    /**
     * Find a model by its primary key or throw an exception
     */
    public static function findOrFail($id) {
        $model = static::find($id);
        
        if (!$model) {
            throw new \Exception("No query results for model [" . static::class . "] {$id}");
        }
        
        return $model;
    }
    
    /**
     * Save the model to the database
     */
    public function save() {
        $table = static::getTableName();
        $primaryKey = static::$primaryKey;
        
        // Set timestamps if enabled
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $this->{static::CREATED_AT} = $now;
            }
            $this->{static::UPDATED_AT} = $now;
        }
        
        if ($this->exists) {
            // Update existing record
            $attributes = $this->getDirty();
            $updates = [];
            $params = [":{$primaryKey}" => $this->$primaryKey];
            
            foreach ($attributes as $key => $value) {
                $updates[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            
            if (empty($updates)) {
                return true; // No changes to update
            }
            
            $sql = "UPDATE {$table} SET " . implode(', ', $updates) . " WHERE {$primaryKey} = :{$primaryKey}";
            
            $stmt = self::getDb()->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $this->syncOriginal();
            }
            
            return $result;
        } else {
            // Insert new record
            $attributes = $this->getAttributes();
            
            // Remove primary key if it's auto-increment
            if (isset($attributes[$primaryKey]) && $attributes[$primaryKey] === null) {
                unset($attributes[$primaryKey]);
            }
            
            $columns = array_keys($attributes);
            $placeholders = array_map(fn($col) => ":{$col}", $columns);
            
            $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                   VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = self::getDb()->prepare($sql);
            
            // Bind parameters
            foreach ($attributes as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->$primaryKey = self::getDb()->lastInsertId();
                $this->exists = true;
                $this->syncOriginal();
            }
            
            return $result;
        }
    }
    
    /**
     * Delete the model from the database
     */
    public function delete() {
        if (!$this->exists) {
            return true;
        }
        
        $table = static::getTableName();
        $primaryKey = static::$primaryKey;
        
        $sql = "DELETE FROM {$table} WHERE {$primaryKey} = :id";
        $stmt = self::getDb()->prepare($sql);
        $result = $stmt->execute([':id' => $this->$primaryKey]);
        
        if ($result) {
            $this->exists = false;
        }
        
        return $result;
    }
    
    /**
     * Get the database connection
     */
    protected static function getDb() {
        if (!self::$db) {
            self::$db = getDbConnection();
        }
        return self::$db;
    }
    
    /**
     * Get the model's attributes
     */
    public function getAttributes() {
        return $this->attributes;
    }
    
    /**
     * Get the model's original attribute values
     */
    protected function getOriginal() {
        return $this->original ?? $this->attributes;
    }
    
    /**
     * Get the attributes that have been changed since the last sync
     */
    public function getDirty() {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Sync the original attributes with the current attributes
     */
    public function syncOriginal() {
        $this->original = $this->attributes;
        return $this;
    }
    
    /**
     * Create a new model instance in memory
     */
    public static function make(array $attributes = []) {
        return new static($attributes);
    }
    
    /**
     * Create a new model and persist it to the database
     */
    public static function create(array $attributes) {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
    
    /**
     * Begin querying the model
     */
    public static function query() {
        return new QueryBuilder(static::class);
    }
    
    /**
     * Handle dynamic static method calls into the model
     */
    public static function __callStatic($method, $parameters) {
        return (new static)->$method(...$parameters);
    }
}
