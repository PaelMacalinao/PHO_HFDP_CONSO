# PHO CONSO HFDP Application

A web-based application for managing PHO CONSO HFDP (Health Facility Development Plan) records with filtering and data entry capabilities.

## Features

- **Dashboard** with interactive filters for:
  - Year (2024-2028)
  - Cluster (REDCATS, BCCL, CAM, NABBrRBEQ-K)
  - Facility Level (BHS, PCF, HOSP)
  - Category (INFRASTRUCTURE, EQUIPMENT, HUMAN RESOURCE, TRANSPORTATION)
  - Fund Source (PLGU, MLGU, DOH, DPWH, NGO, and combinations)
  - Presence in Existing Plans (LIPH, LIDP, AIP, CDP, DTP, AOP, NONE)

- **Data Entry Form** for creating new records
- **Edit and Delete** functionality for existing records
- **Responsive Design** for desktop and mobile devices

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache web server (XAMPP recommended)
- Modern web browser

## Installation

1. **Extract files** to your web server directory:
   ```
   C:\xampp\htdocs\PHO_HFDP_CONSO\
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database/schema.sql` file, OR
   - Run the SQL commands manually to create the database and table

3. **Configure database connection**:
   - Edit `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'pho_conso_hfdp');
     ```

4. **Start Apache and MySQL** services in XAMPP Control Panel

5. **Optional — Add logos:**  
   Copy the Province of Palawan and Provincial Health Office logos into `assets/images/` as:
   - `palawan-logo.png`
   - `pho-logo.png`  
   The header will display them automatically; if the files are missing, the layout still works without images.

6. **Access the application**:
   - Open your browser and navigate to:
     ```
     http://localhost/PHO_HFDP_CONSO/
     ```

## Design

The interface uses a color scheme based on the **Province of Palawan** and **Provincial Health Office** logos: greens, gold/yellow accents, Palawan blue, and brown/tan. The header can show both logos when image files are placed in `assets/images/`.

## File Structure

```
PHO_HFDP_CONSO/
├── api/
│   ├── get_records.php      # Fetch records with filters
│   ├── create_record.php    # Create new record
│   ├── update_record.php    # Update existing record
│   └── delete_record.php    # Delete record
├── assets/
│   ├── css/
│   │   └── style.css        # Application styles
│   ├── images/              # Add palawan-logo.png & pho-logo.png here
│   └── js/
│       ├── app.js           # Dashboard JavaScript
│       └── form.js          # Form JavaScript
├── config/
│   └── database.php         # Database configuration
├── database/
│   └── schema.sql           # Database schema
├── index.php                # Dashboard page
├── form.php                 # Data entry form
└── README.md                # This file
```

## Database Schema

The main table `hfdp_records` contains the following columns:

- `id` - Primary key (auto-increment)
- `year` - Year (INT)
- `cluster` - Cluster name (VARCHAR)
- `concerned_office_facility` - Office/Facility name (VARCHAR)
- `facility_level` - BHS/PCF/HOSP (ENUM)
- `category` - INFRASTRUCTURE/EQUIPMENT/HUMAN RESOURCE/TRANSPORTATION (ENUM)
- `type_of_health_facility` - Type description (VARCHAR)
- `number_of_units` - Number of units (INT)
- `facilities` - Specific item description (TEXT)
- `target` - Target information (VARCHAR)
- `costing` - Cost amount (DECIMAL)
- `fund_source` - Funding source (VARCHAR)
- `presence_in_existing_plans` - Plan presence (VARCHAR)
- `remarks` - Additional remarks (TEXT)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Usage

### Adding a New Record

1. Click on "Add New Record" in the navigation menu
2. Fill in the required fields (marked with *)
3. Click "Save Record"
4. You will be redirected to the dashboard

### Filtering Records

1. Use the filter dropdowns on the dashboard
2. Select your desired filter values
3. Click "Apply Filters" to filter the records
4. Click "Clear Filters" to reset all filters

### Editing a Record

1. On the dashboard, click the "Edit" button next to a record
2. Modify the fields in the modal form
3. Click "Update Record" to save changes

### Deleting a Record

1. On the dashboard, click the "Delete" button next to a record
2. Confirm the deletion in the popup dialog

## API Endpoints

- `GET api/get_records.php` - Get records with optional filters
  - Query parameters: `year`, `cluster`, `facility_level`, `category`, `fund_source`, `presence_plans`
  
- `POST api/create_record.php` - Create a new record
  - Body: JSON object with record data
  
- `POST api/update_record.php` - Update an existing record
  - Body: JSON object with record data including `id`
  
- `POST api/delete_record.php` - Delete a record
  - Body: JSON object with `id` field

## Notes

- The application does not import actual CSV row data - it only sets up the schema and interface
- All amounts are stored as DECIMAL(15,2) for precise financial calculations
- The application uses prepared statements to prevent SQL injection
- All user inputs are sanitized before database operations

## Troubleshooting

**Database connection error:**
- Check if MySQL service is running in XAMPP
- Verify database credentials in `config/database.php`
- Ensure the database `pho_conso_hfdp` exists

**Page not loading:**
- Check if Apache service is running
- Verify the file path is correct
- Check browser console for JavaScript errors

**Filters not working:**
- Check browser console for errors
- Verify API endpoints are accessible
- Check database connection

## Support

For issues or questions, please check:
- PHP error logs in XAMPP
- Browser developer console for JavaScript errors
- MySQL error logs
