# Configuración de SSH sin Contraseña para Despliegues

Este documento describe cómo configurar la autenticación SSH por llave pública para evitar ingresar contraseñas repetidamente durante el despliegue de la aplicación Siloe.

## Pasos para la configuración

1. **Genera un par de llaves SSH** (si aún no lo tienes):
   ```bash
   ssh-keygen -t ed25519 -C "tu_correo@ejemplo.com"
   ```
   - Presiona Enter para aceptar la ubicación predeterminada (`~/.ssh/id_ed25519`)
   - Opcionalmente, configura una frase de contraseña para mayor seguridad

2. **Copia la llave pública al servidor**:
   ```bash
   ssh-copy-id siloecom@192.185.143.154
   ```
   - Te pedirá la contraseña del servidor por última vez

3. **Configura un alias en tu archivo `~/.ssh/config`** (opcional):
   ```
   Host siloe
       HostName 192.185.143.154
       User siloecom
       IdentityFile ~/.ssh/id_ed25519
   ```

4. **Actualiza el script de despliegue** para usar tu llave SSH:
   - Edita `deploy/scripts/deploy_rsync.sh`
   - Modifica la línea que establece `SSH_OPTS`:
     ```bash
     : "${SSH_OPTS:=-i ~/.ssh/id_ed25519}"
     ```

## Uso después de la configuración

- Para conectarte al servidor: `ssh siloe` (si configuraste el alias) o `ssh siloecom@192.185.143.154`
- Para desplegar cambios: `./deploy/scripts/deploy_rsync.sh`

## Ventajas

- Sin necesidad de ingresar contraseñas en cada conexión
- Mayor seguridad que las contraseñas simples
- Despliegues más rápidos y eficientes

## Recordatorio

Asegúrate de mantener tu llave privada segura. Si usas múltiples dispositivos, necesitarás repetir este proceso en cada uno de ellos.
