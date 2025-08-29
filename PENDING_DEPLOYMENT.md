# Pending Deployment

## Ready for Production
- [x] app/Controllers/MenuController.php - Fixed HTTP 500 error, CSRF token issue, and missing getCategories method
- [x] app/views/menus/create.php - Fixed JavaScript syntax error in form submission handling
- [x] app/views/orders/show.php - Fixed flickering popup by converting form submissions to AJAX calls
- [x] app/views/admin/dashboard.php - Added personalized greeting "¡Hola User!"
- [x] app/Controllers/Admin/CompanyController.php - Fixed constructor, imports, and view() method call
- [x] app/views/hr/menu_selections/today.php - Fixed field name mismatch (employee_name -> user_name)
- [x] app/Models/Company.php - Fixed duplicate getDbConnection() function declaration
- [x] app/Models/User.php - Fixed duplicate getDbConnection() function declaration

## Deployed to Production
- [x] app/Core/Router.php - Complete MVC routing system
- [x] app/Core/Request.php - HTTP request handling
- [x] app/Core/Response.php - HTTP response handling  
- [x] app/Core/Session.php - Session management
- [x] app/Core/View.php - View rendering system
- [x] app/Controllers/Admin/DashboardController.php - Admin dashboard
- [x] app/views/layouts/app.php - Main application layout
- [x] app/views/admin/dashboard.php - Admin dashboard view
- [x] app/config/config.php - Application configuration
- [x] app/routes/web.php - Route definitions
- [x] public/index.php - Application entry point
- [x] app/Controllers/EmployeeMenuController.php - Weekly Menu Selection page: set title and active nav (deployed 2025-08-27 16:54:51 -03)
- [x] app/views/employee/menu_selection.php - Converted to partial for proper layout/CSS (deployed 2025-08-27 16:54:51 -03)
- [x] Login system with proper authentication
