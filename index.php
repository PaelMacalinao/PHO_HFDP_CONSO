<?php
/**
 * PHO CONSO HFDP Dashboard
 * Main dashboard page with filters and data table
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userRole = $_SESSION['role'] ?? 'staff';
$assignedFacility = $_SESSION['assigned_facility'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-user-role="<?php echo htmlspecialchars($userRole); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHO CONSO HFDP - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="page-preloader" class="page-preloader" aria-hidden="false">
        <div class="page-preloader-inner">
            <img src="assets/images/pho-logo.png" alt="" class="page-preloader-logo" onerror="this.style.display='none'">
            <div class="page-preloader-spinner"></div>
            <p class="page-preloader-text">Loading...</p>
        </div>
    </div>
    <aside class="sidebar" id="sidebar" aria-label="Main navigation">
        <div class="sidebar-brand">
            <img src="assets/images/pho-logo.png" alt="" class="sidebar-logo-img" onerror="this.style.display='none'">
            <span class="sidebar-brand-text">PHO HFDP</span>
        </div>
        <div class="sidebar-admin">
            <div class="sidebar-admin-inner">
                <div class="sidebar-admin-avatar" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                </div>
                <div class="sidebar-admin-info">
                    <span class="sidebar-admin-role"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                    <span class="sidebar-admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'phoadmin'); ?></span>
                </div>
            </div>
            <a href="logout.php" class="sidebar-signout">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                Sign Out
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link active">
                <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
                <span class="sidebar-label">Dashboard</span>
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="sidebar-section">
                <span class="sidebar-section-title">Settings</span>
                <a href="user_management.php" class="sidebar-link">
                    <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span>
                    <span class="sidebar-label">Add New User/Staff</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>
    <div class="main-wrap" id="main-wrap">
    <div class="container">
        <header class="admin-header">
            <div class="admin-header-inner">
                <div class="admin-header-left">
                    <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle menu" aria-expanded="false">
                        <span class="hamburger"><span></span><span></span><span></span></span>
                    </button>
                    <div class="admin-logo-placeholder" aria-hidden="true">
                        <img src="assets/images/pho-logo.png" alt="" class="admin-logo-img" onerror="this.style.display='none';this.parentElement.classList.add('no-img')">
                        <span class="admin-logo-fallback">PHO</span>
                    </div>
                    <div class="admin-header-title">
                        <h1>PHO CONSO HFDP Dashboard</h1>
                        <span class="admin-header-subtitle">Provincial Health Office · Province of Palawan</span>
                    </div>
                </div>
                <div class="admin-header-right">
                    <div class="admin-header-meta">
                        <div class="admin-clock">
                            <div class="clock-date" id="clock-date">-- -- ----</div>
                            <div class="clock-time" id="clock-time">--:--:-- --</div>
                        </div>
                        <div class="admin-user-wrap">
                            <button type="button" class="admin-trigger" id="admin-trigger" aria-expanded="false" aria-haspopup="true">
                                <span class="admin-trigger-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                                </span>
                                <span class="admin-trigger-label"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                            </button>
                            <div class="admin-dropdown" id="admin-dropdown" role="menu" aria-hidden="true">
                                <div class="admin-dropdown-header">
                                    <span class="admin-dropdown-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                                    </span>
                                    <div class="admin-dropdown-info">
                                        <span class="admin-dropdown-role"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                                        <span class="admin-dropdown-username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'phoadmin'); ?></span>
                                    </div>
                                </div>
                                <div class="admin-dropdown-sep"></div>
                                <a href="logout.php" class="admin-dropdown-signout" role="menuitem">
                                    <span class="admin-dropdown-signout-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                    </span>
                                    Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="message error" style="margin: 20px; padding: 14px; border-radius: var(--radius-sm); background: var(--pho-danger-bg); color: var(--pho-danger); border-left: 4px solid var(--pho-danger);">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="filters-section">
            <div class="filters-header-row">
                <div>
                    <h2>Filters</h2>
                    <div class="filters-subtitle">Start with the primary filters, then refine by funding and planning.</div>
                </div>
                <div class="filter-actions">
                    <button id="btn-apply-filters" class="btn btn-primary">Apply Filters</button>
                    <button id="btn-clear-filters" class="btn btn-secondary">Clear</button>
                </div>
            </div>

            <div class="filters-panels">
                <div class="filters-panel">
                    <div class="filters-panel-title">Primary</div>
                    <div class="filters-grid filters-grid-primary">
                        <div class="filter-group">
                            <label for="filter-year">Year</label>
                            <select id="filter-year">
                                <option value="">All Years</option>
                                <?php for ($year = 2024; $year <= 2100; $year++): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-cluster">Cluster</label>
                            <select id="filter-cluster">
                                <option value="">All Clusters</option>
                                <option value="REDCATS">REDCATS</option>
                                <option value="BCCL">BCCL</option>
                                <option value="CAM">CAM</option>
                                <option value="NABBrRBEQ-K">NABBrRBEQ-K</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-facility-level">Facility Level</label>
                            <select id="filter-facility-level">
                                <option value="">All Levels</option>
                                <option value="BHS">BHS (Barangay Health Station)</option>
                                <option value="PCF">PCF (Primary Care Facility)</option>
                                <option value="HOSP">HOSP (Hospital)</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-category">Category</label>
                            <select id="filter-category">
                                <option value="">All Categories</option>
                                <option value="INFRASTRUCTURE">INFRASTRUCTURE</option>
                                <option value="EQUIPMENT">EQUIPMENT</option>
                                <option value="HUMAN RESOURCE">HUMAN RESOURCE</option>
                                <option value="TRANSPORTATION">TRANSPORTATION</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-type-of-health-facility">Type of Health Facility</label>
                            <select id="filter-type-of-health-facility">
                                <option value="">All Types</option>
                                <optgroup label="A–C">
                                    <option value="ADMINISTRATIVE AIDE I">ADMINISTRATIVE AIDE I</option>
                                    <option value="ACCOUNTANT I">ACCOUNTANT I</option>
                                    <option value="ADMIN AIDE I">ADMIN AIDE I</option>
                                    <option value="ADMIN ASSISTANT">ADMIN ASSISTANT</option>
                                    <option value="ADMIN ASSISTANT II">ADMIN ASSISTANT II</option>
                                    <option value="ADMIN OFFICER">ADMIN OFFICER</option>
                                    <option value="ADMIN OFFICER IV">ADMIN OFFICER IV</option>
                                    <option value="ADMIN OFFICER V">ADMIN OFFICER V</option>
                                    <option value="ADMINISTRATIVE & TECHNICAL">ADMINISTRATIVE & TECHNICAL</option>
                                    <option value="ADMINISTRATIVE AIDE">ADMINISTRATIVE AIDE</option>
                                    <option value="ADMINISTRATIVE AIDE I (DRIVER I)">ADMINISTRATIVE AIDE I (DRIVER I)</option>
                                    <option value="ADMINISTRATIVE AIDE I (FOOD SERVICE WORKER)">ADMINISTRATIVE AIDE I (FOOD SERVICE WORKER)</option>
                                    <option value="ADMINISTRATIVE AIDE I (MEDICAL RECORDS CLERK)">ADMINISTRATIVE AIDE I (MEDICAL RECORDS CLERK)</option>
                                    <option value="ADMINISTRATIVE AIDE I (PHARMACY AIDE)">ADMINISTRATIVE AIDE I (PHARMACY AIDE)</option>
                                    <option value="ADMINISTRATIVE AIDE I (UTILITY)">ADMINISTRATIVE AIDE I (UTILITY)</option>
                                    <option value="ADMINISTRATIVE AIDE I (WATCHMAN)">ADMINISTRATIVE AIDE I (WATCHMAN)</option>
                                    <option value="ADMINISTRATIVE AIDE II">ADMINISTRATIVE AIDE II</option>
                                    <option value="ADMINISTRATIVE AIDE II (BILLING)">ADMINISTRATIVE AIDE II (BILLING)</option>
                                    <option value="ADMINISTRATIVE AIDE II (CASH CLERK)">ADMINISTRATIVE AIDE II (CASH CLERK)</option>
                                    <option value="ADMINISTRATIVE AIDE II (CLAIMS)">ADMINISTRATIVE AIDE II (CLAIMS)</option>
                                    <option value="ADMINISTRATIVE AIDE II (HEALTH">ADMINISTRATIVE AIDE II (HEALTH</option>
                                    <option value="ADMINISTRATIVE AIDE II (SUPPLY AIDE)">ADMINISTRATIVE AIDE II (SUPPLY AIDE)</option>
                                    <option value="ADMINISTRATIVE AIDE IV">ADMINISTRATIVE AIDE IV</option>
                                    <option value="ADMINISTRATIVE AIDE VI">ADMINISTRATIVE AIDE VI</option>
                                    <option value="ADMINISTRATIVE ASSISTANT">ADMINISTRATIVE ASSISTANT</option>
                                    <option value="ADMINISTRATIVE ASSISTANT I">ADMINISTRATIVE ASSISTANT I</option>
                                    <option value="ADMINISTRATIVE ASSISTANT II">ADMINISTRATIVE ASSISTANT II</option>
                                    <option value="ADMINISTRATIVE ASSISTANT III">ADMINISTRATIVE ASSISTANT III</option>
                                    <option value="ADMINISTRATIVE OFFICER">ADMINISTRATIVE OFFICER</option>
                                    <option value="ADMINISTRATIVE OFFICER II">ADMINISTRATIVE OFFICER II</option>
                                    <option value="ADMINISTRATIVE OFFICER III">ADMINISTRATIVE OFFICER III</option>
                                    <option value="ADMINISTRATIVE OFFICER III (HUMAN RESOURCE OFFICER)">ADMINISTRATIVE OFFICER III (HUMAN RESOURCE OFFICER)</option>
                                    <option value="ADMINISTRATIVE OFFICER IV">ADMINISTRATIVE OFFICER IV</option>
                                    <option value="ADMINISTRATIVE OFFICER V">ADMINISTRATIVE OFFICER V</option>
                                    <option value="ADMINISTRATIVE STAFF">ADMINISTRATIVE STAFF</option>
                                    <option value="ADMINITRATIVE ASSISTANT II">ADMINITRATIVE ASSISTANT II</option>
                                    <option value="ADMINSTRATIVE OFFICER">ADMINSTRATIVE OFFICER</option>
                                    <option value="AMBULANCE/PTV DRIVER">AMBULANCE/PTV DRIVER</option>
                                    <option value="ASSISTANT NUTRITIONIST">ASSISTANT NUTRITIONIST</option>
                                    <option value="ASST MHO">ASST MHO</option>
                                    <option value="BILLING CLERK">BILLING CLERK</option>
                                    <option value="BOATSWAIN">BOATSWAIN</option>
                                    <option value="CASH CLERK">CASH CLERK</option>
                                    <option value="CASHIER">CASHIER</option>
                                    <option value="CLAIMS PROCESSOR">CLAIMS PROCESSOR</option>
                                    <option value="CLERK">CLERK</option>
                                    <option value="CLERK/UTILITY">CLERK/UTILITY</option>
                                    <option value="COMPUTER FILE LIBRARIAN">COMPUTER FILE LIBRARIAN</option>
                                    <option value="COMPUTER MAINTENANCE TECHNICIAN I">COMPUTER MAINTENANCE TECHNICIAN I</option>
                                    <option value="COMPUTER MAINTENANCE TECHNOLOGIST">COMPUTER MAINTENANCE TECHNOLOGIST</option>
                                    <option value="COMPUTER OPERATOR I">COMPUTER OPERATOR I</option>
                                    <option value="CONSTRCUTION OF PCF">CONSTRCUTION OF PCF</option>
                                    <option value="CONSTRUCTION OF BHS">CONSTRUCTION OF BHS</option>
                                    <option value="CONSTRUCTION OF HOSPITAL">CONSTRUCTION OF HOSPITAL</option>
                                    <option value="CONSTRUCTION OF PCF">CONSTRUCTION OF PCF</option>
                                    <option value="CONTACT TRACER">CONTACT TRACER</option>
                                    <option value="COOK">COOK</option>
                                    <option value="COOK II">COOK II</option>
                                </optgroup>
                                <optgroup label="D–L">
                                    <option value="DATA CONTROLLER">DATA CONTROLLER</option>
                                    <option value="DATA ENCODER">DATA ENCODER</option>
                                    <option value="DENTAL AIDE">DENTAL AIDE</option>
                                    <option value="DENTIST">DENTIST</option>
                                    <option value="DENTIST I">DENTIST I</option>
                                    <option value="DENTIST II">DENTIST II</option>
                                    <option value="DESIGNATED REGULATORY COMPLIANCE">DESIGNATED REGULATORY COMPLIANCE</option>
                                    <option value="DIALYSIS TECH">DIALYSIS TECH</option>
                                    <option value="DOCTOR">DOCTOR</option>
                                    <option value="DRIVER">DRIVER</option>
                                    <option value="DRIVER II">DRIVER II</option>
                                    <option value="DUMP DRIVER">DUMP DRIVER</option>
                                    <option value="ELECTRICIAN">ELECTRICIAN</option>
                                    <option value="EMERGENCY TRANSPORT DRIVER">EMERGENCY TRANSPORT DRIVER</option>
                                    <option value="ENCODER">ENCODER</option>
                                    <option value="ENCODER/ADMIN ASSISTANT">ENCODER/ADMIN ASSISTANT</option>
                                    <option value="ENGINEER">ENGINEER</option>
                                    <option value="ENGINEER II">ENGINEER II</option>
                                    <option value="ENGINEERI">ENGINEERI</option>
                                    <option value="HEALTH AIDE">HEALTH AIDE</option>
                                    <option value="HEALTH EDUCATION & PROMOTION OFFICER">HEALTH EDUCATION & PROMOTION OFFICER</option>
                                    <option value="HEALTH EQUIPMENT">HEALTH EQUIPMENT</option>
                                    <option value="HEALTH PROGRAM OFFICER I">HEALTH PROGRAM OFFICER I</option>
                                    <option value="HEPO I">HEPO I</option>
                                    <option value="HOSPITAL">HOSPITAL</option>
                                    <option value="HOSPITAL EQUIPMENT">HOSPITAL EQUIPMENT</option>
                                    <option value="HUMAN RESOURCE FOR HEALTH">HUMAN RESOURCE FOR HEALTH</option>
                                    <option value="HUMAN RESOURCE OFFICER">HUMAN RESOURCE OFFICER</option>
                                    <option value="INFORMATION SYSTEM ANALYST">INFORMATION SYSTEM ANALYST</option>
                                    <option value="INFORMATION TECHNOLOGIST I">INFORMATION TECHNOLOGIST I</option>
                                    <option value="INFORMATION TECHNOLOGY OFFICER">INFORMATION TECHNOLOGY OFFICER</option>
                                    <option value="IT">IT</option>
                                    <option value="IT PERSONNEL">IT PERSONNEL</option>
                                    <option value="LAB AIDE">LAB AIDE</option>
                                    <option value="LABORATORY AIDE">LABORATORY AIDE</option>
                                    <option value="LABORATORY PERSONNEL">LABORATORY PERSONNEL</option>
                                    <option value="LABORATORY TECHNICIAN">LABORATORY TECHNICIAN</option>
                                    <option value="LABORATORY TECHNICIAN I">LABORATORY TECHNICIAN I</option>
                                    <option value="LAND VEHICLE DRIVER">LAND VEHICLE DRIVER</option>
                                    <option value="LAUNDRY WORKER">LAUNDRY WORKER</option>
                                    <option value="LAUNDRY WORKER II">LAUNDRY WORKER II</option>
                                </optgroup>
                                <optgroup label="M–N">
                                    <option value="MAINTENANCE PERSONNEL">MAINTENANCE PERSONNEL</option>
                                    <option value="MALARIA VOLUNTEER">MALARIA VOLUNTEER</option>
                                    <option value="MANPOWER">MANPOWER</option>
                                    <option value="MED TECH I">MED TECH I</option>
                                    <option value="MED TECH III">MED TECH III</option>
                                    <option value="MEDICAL DOCTOR">MEDICAL DOCTOR</option>
                                    <option value="MEDICAL EQUIPMENT TECHNICIAL III">MEDICAL EQUIPMENT TECHNICIAL III</option>
                                    <option value="MEDICAL EQUIPMENT TECHNICIAN">MEDICAL EQUIPMENT TECHNICIAN</option>
                                    <option value="MEDICAL EQUIPMENT TECHNICIAN III">MEDICAL EQUIPMENT TECHNICIAN III</option>
                                    <option value="MEDICAL LABORATORY TECHNICIAN">MEDICAL LABORATORY TECHNICIAN</option>
                                    <option value="MEDICAL OFFICER">MEDICAL OFFICER</option>
                                    <option value="MEDICAL OFFICER III">MEDICAL OFFICER III</option>
                                    <option value="MEDICAL OFFICER IV">MEDICAL OFFICER IV</option>
                                    <option value="MEDICAL RECORDS OFFICER">MEDICAL RECORDS OFFICER</option>
                                    <option value="MEDICAL RECORDS OFFICER I">MEDICAL RECORDS OFFICER I</option>
                                    <option value="MEDICAL RECORDS OFFICER II">MEDICAL RECORDS OFFICER II</option>
                                    <option value="MEDICAL SPECIALIST">MEDICAL SPECIALIST</option>
                                    <option value="MEDICAL SPECIALIST I">MEDICAL SPECIALIST I</option>
                                    <option value="MEDICAL SPECIALIST II">MEDICAL SPECIALIST II</option>
                                    <option value="MEDICAL SPECIALIST III">MEDICAL SPECIALIST III</option>
                                    <option value="MEDICAL SPECIALISTS">MEDICAL SPECIALISTS</option>
                                    <option value="MEDICAL TECHNOLOGIST">MEDICAL TECHNOLOGIST</option>
                                    <option value="MEDICAL TECHNOLOGIST I">MEDICAL TECHNOLOGIST I</option>
                                    <option value="MEDICAL TECHNOLOGIST II">MEDICAL TECHNOLOGIST II</option>
                                    <option value="MEDICAL TECHNOLOGIST III">MEDICAL TECHNOLOGIST III</option>
                                    <option value="MEDTECH">MEDTECH</option>
                                    <option value="MEDTECH I">MEDTECH I</option>
                                    <option value="MEDTECH II">MEDTECH II</option>
                                    <option value="MEDTECH III">MEDTECH III</option>
                                    <option value="MIDWIFE">MIDWIFE</option>
                                    <option value="MIDWIFE I">MIDWIFE I</option>
                                    <option value="MIDWIFE II">MIDWIFE II</option>
                                    <option value="MIDWIFE III">MIDWIFE III</option>
                                    <option value="MIDWIFEI">MIDWIFEI</option>
                                    <option value="MIDWIVES">MIDWIVES</option>
                                    <option value="MUNICIPAL NUTRITION ACTION">MUNICIPAL NUTRITION ACTION</option>
                                    <option value="NURSE">NURSE</option>
                                    <option value="NURSE I">NURSE I</option>
                                    <option value="NURSE II">NURSE II</option>
                                    <option value="NURSE III">NURSE III</option>
                                    <option value="NURSE IV">NURSE IV</option>
                                    <option value="NURSE V">NURSE V</option>
                                    <option value="NURSEI">NURSEI</option>
                                    <option value="NURSES">NURSES</option>
                                    <option value="NURSING AIDE">NURSING AIDE</option>
                                    <option value="NURSING ATTENDANDANT I">NURSING ATTENDANDANT I</option>
                                    <option value="NURSING ATTENDANT">NURSING ATTENDANT</option>
                                    <option value="NURSING ATTENDANT I">NURSING ATTENDANT I</option>
                                    <option value="NURSING ATTENDANT II">NURSING ATTENDANT II</option>
                                    <option value="NUTRITION ACTION OFFICER">NUTRITION ACTION OFFICER</option>
                                    <option value="NUTRITIONIST">NUTRITIONIST</option>
                                    <option value="NUTRITIONIST DIETITIAN">NUTRITIONIST DIETITIAN</option>
                                    <option value="NUTRITIONIST-DIETICIAN">NUTRITIONIST-DIETICIAN</option>
                                </optgroup>
                                <optgroup label="P–Z">
                                    <option value="PERMANENT">PERMANENT</option>
                                    <option value="PERSONNEL">PERSONNEL</option>
                                    <option value="PHARMACIST">PHARMACIST</option>
                                    <option value="PHARMACIST I">PHARMACIST I</option>
                                    <option value="PHARMACIST II">PHARMACIST II</option>
                                    <option value="PHARMACISTI">PHARMACISTI</option>
                                    <option value="PHARMACY AIDE I">PHARMACY AIDE I</option>
                                    <option value="PHARMACY ASSISTANT">PHARMACY ASSISTANT</option>
                                    <option value="PHYSICAL THERAPIST">PHYSICAL THERAPIST</option>
                                    <option value="PHYSICAL THERAPIST II">PHYSICAL THERAPIST II</option>
                                    <option value="PHYSICIAN">PHYSICIAN</option>
                                    <option value="POP COM OFFICER">POP COM OFFICER</option>
                                    <option value="PRIMARY CUSTODIAN">PRIMARY CUSTODIAN</option>
                                    <option value="PSYCHOLOGIST">PSYCHOLOGIST</option>
                                    <option value="RAD TECH I">RAD TECH I</option>
                                    <option value="RADIO TECHNICIAN">RADIO TECHNICIAN</option>
                                    <option value="RADIO TECHNOLOGIST">RADIO TECHNOLOGIST</option>
                                    <option value="RADIOLOGIC TECHNOLOGIST">RADIOLOGIC TECHNOLOGIST</option>
                                    <option value="RADIOLOGIC TECHNOLOGIST I">RADIOLOGIC TECHNOLOGIST I</option>
                                    <option value="RADTECH">RADTECH</option>
                                    <option value="RADTECH I">RADTECH I</option>
                                    <option value="RADTECH III">RADTECH III</option>
                                    <option value="RESPIRATORY THERAPIST">RESPIRATORY THERAPIST</option>
                                    <option value="SANITARY INSPECTOR">SANITARY INSPECTOR</option>
                                    <option value="SANITATION INSPECTOR">SANITATION INSPECTOR</option>
                                    <option value="SANITATION INSPECTOR IV">SANITATION INSPECTOR IV</option>
                                    <option value="SEA AMBULANCE DRIVER">SEA AMBULANCE DRIVER</option>
                                    <option value="SEA CAPTAIN">SEA CAPTAIN</option>
                                    <option value="SEAMSTRESS">SEAMSTRESS</option>
                                    <option value="SOCIAL WELFARE AIDE II">SOCIAL WELFARE AIDE II</option>
                                    <option value="SOCIAL WELFARE ASSISTANT">SOCIAL WELFARE ASSISTANT</option>
                                    <option value="SOCIAL WELFARE OFFICER I">SOCIAL WELFARE OFFICER I</option>
                                    <option value="SOCIAL WELFARE OFFICER II">SOCIAL WELFARE OFFICER II</option>
                                    <option value="SOCIAL WLELFARE OFFICER I">SOCIAL WLELFARE OFFICER I</option>
                                    <option value="SOCIAL WORK OFFICER I">SOCIAL WORK OFFICER I</option>
                                    <option value="SOCIAL WORKER III">SOCIAL WORKER III</option>
                                    <option value="SPRAYMEN">SPRAYMEN</option>
                                    <option value="SUPERVISING ADMINISTRATIVE OFFICER">SUPERVISING ADMINISTRATIVE OFFICER</option>
                                    <option value="SUPPLY OFFICER II">SUPPLY OFFICER II</option>
                                    <option value="TRAINING">TRAINING</option>
                                    <option value="TRAININGS">TRAININGS</option>
                                    <option value="TRANSPORTATION EQUIPMENT">TRANSPORTATION EQUIPMENT</option>
                                    <option value="UTILITY">UTILITY</option>
                                    <option value="UTILITY WORKER">UTILITY WORKER</option>
                                    <option value="UTILITY WORKER AND BHW">UTILITY WORKER AND BHW</option>
                                    <option value="UTILITY WORKER I">UTILITY WORKER I</option>
                                    <option value="WATCHMAN II">WATCHMAN II</option>
                                    <option value="WATCHMAN III">WATCHMAN III</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="filters-panel">
                    <div class="filters-panel-title">Funding & Plans</div>
                    <div class="filters-grid filters-grid-secondary">
                        <div class="filter-group">
                            <label for="filter-fund-source">Fund Source</label>
                            <select id="filter-fund-source">
                                <option value="">All Fund Sources</option>
                                <option value="PLGU">PLGU</option>
                                <option value="MLGU">MLGU</option>
                                <option value="DOH">DOH</option>
                                <option value="DPWH">DPWH</option>
                                <option value="NGO">NGO</option>
                                <option value="MLGU/PLGU">MLGU/PLGU</option>
                                <option value="MLGU/DOH">MLGU/DOH</option>
                                <option value="PLGU/DOH">PLGU/DOH</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-presence-plans">Presence in Existing Plans</label>
                            <select id="filter-presence-plans">
                                <option value="">All</option>
                                <option value="LIPH">LIPH</option>
                                <option value="LIDP">LIDP</option>
                                <option value="AIP">AIP</option>
                                <option value="CDP">CDP</option>
                                <option value="DTP">DTP</option>
                                <option value="AOP">AOP</option>
                                <option value="NONE">NONE</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-target">Target</label>
                            <select id="filter-target">
                                <option value="">All Targets</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="data-section">
            <div class="section-header">
                <h2>Records</h2>
                <div class="section-header-right">
                    <span class="record-count"><span id="record-count">0</span> record(s) found</span>
                    <button type="button" id="btn-export-excel" class="btn btn-excel" title="Export to Excel (respects current filters)">
                        <span class="btn-excel-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-5V4zM8 12h2v2H8v-2zm0 4h2v2H8v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2z"/></svg>
                        </span>
                        Export to Excel
                    </button>
                    <a href="form.php" class="btn btn-primary">Add New Record</a>
                </div>
            </div>

            <div class="table-container">
                <table id="records-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Cluster</th>
                            <th>Concerned Office/Facility</th>
                            <th>Facility Level</th>
                            <th>Category</th>
                            <th>Type of Health Facility</th>
                            <th>Number of Units</th>
                            <th>Target</th>
                            <th>Costing</th>
                            <th>Fund Source</th>
                            <th>Presence in Existing Plans</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="records-tbody">
                        <tr>
                            <td colspan="13" class="no-data">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Record</h2>
            <div id="edit-form-container"></div>
        </div>
    </div>

    <script src="assets/js/target-options.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/clock.js"></script>
    <script src="assets/js/admin-menu.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script src="assets/js/preloader.js"></script>
    <script src="assets/js/rbac.js"></script>
    <script>
        // Populate Target filter dropdown on page load
        document.addEventListener('DOMContentLoaded', function() {
            const targetFilter = document.getElementById('filter-target');
            if (targetFilter) {
                targetFilter.innerHTML = generateTargetOptions('');
            }
        });
    </script>
</body>
</html>
