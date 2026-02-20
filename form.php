<?php
/**
 * PHO CONSO HFDP Data Entry Form
 * Form for adding new records
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// --- Smart auto-fill logic for staff users ---
$assignedFacility = $_SESSION['assigned_facility'] ?? '';
$isStaff = ($_SESSION['role'] ?? '') === 'staff';
$hasAssignedFacility = $isStaff && !empty($assignedFacility);

// Determine if facility is a hospital
$isHospital = $hasAssignedFacility && stripos($assignedFacility, 'HOSPITAL') !== false;

// Derive municipality from session or facility map
$autoMunicipality = $_SESSION['municipality'] ?? '';
if ($hasAssignedFacility && empty($autoMunicipality)) {
    $facilityMunicipalityMap = [
        'LINAPACAN MUNICIPAL HEALTH OFFICE' => 'LINAPACAN',
        'CULION MUNICIPAL HEALTH OFFICE' => 'CULION',
        'BUSUANGA HEALTH OFFICE' => 'BUSUANGA',
        'CORON DISTRICT HOSPITAL' => 'CORON',
        'CORON MUNICIPAL HEALTH OFFICE' => 'CORON',
        'AGUTAYA MUNICIPAL HEALTH OFFICE' => 'AGUTAYA',
        'CUYO DISTRICT HOSPITAL' => 'CUYO',
        'CUYO MUNICIPAL HEALTH OFFICE' => 'CUYO',
        'MAGSAYSAY MUNICIPAL HEALTH OFFICE' => 'MAGSAYSAY',
        'ABORLAN MUNICIPAL HEALTH OFFICE' => 'ABORLAN',
        'ABORLAN MEDICARE HOSPITAL' => 'ABORLAN',
        'BALABAC DISTRICT HOSPITAL' => 'BALABAC',
        'BALABAC MUNICIPAL HEALTH OFFICE' => 'BALABAC',
        'BATARAZA DISTRICT HOSPITAL' => 'BATARAZA',
        'BATARAZA MUNICIPAL HEALTH OFFICE' => 'BATARAZA',
        "BROOKE'S POINT MUNICIPAL HEALTH OFFICE" => "BROOKE'S POINT",
        'SOUTHERN PALAWAN PROVINCIAL HOSPITAL' => "BROOKE'S POINT",
        'DR. JOSE RIZAL DISTRICT HOSPITAL' => 'RIZAL',
        'RIZAL MUNICIPAL HEALTH OFFICE' => 'RIZAL',
        'KALAYAAN MUNICIPAL HEALTH OFFICE' => 'KALAYAAN',
        'NARRA MUNICIPAL HOSPITAL' => 'NARRA',
        'NARRA MUNICIPAL HEALTH OFFICE' => 'NARRA',
        'QUEZON MEDICARE HOSPITAL' => 'QUEZON',
        'QUEZON MUNICIPAL HEALTH OFFICE' => 'QUEZON',
        'SOFRONIO ESPAÑOLA DISTRICT HOSPITAL' => 'SOFRONIO ESPAÑOLA',
        'SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE' => 'SOFRONIO ESPAÑOLA',
        'ARACELI MUNICIPAL HEALTH OFFICE' => 'ARACELI',
        'ARACELI-DUMARAN DISTRICT HOSPITAL' => 'DUMARAN',
        'CAGAYANCILLO MUNICIPAL HEALTH OFFICE' => 'CAGAYANCILLO',
        'DUMARAN MUNICIPAL HEALTH OFFICE' => 'DUMARAN',
        'FRANCISCO F. PONCE DE LEON HOSPITAL' => 'DUMARAN',
        'EL NIDO COMMUNITY HOSPITAL' => 'EL NIDO',
        'EL NIDO MUNICIPAL HEALTH OFFICE' => 'EL NIDO',
        'NORTHERN PALAWAN PROVINCIAL HOSPITAL' => 'TAYTAY',
        'ROXAS MEDICARE HOSPITAL' => 'ROXAS',
        'ROXAS MUNICIPAL HEALTH OFFICE' => 'ROXAS',
        'SAN VICENTE DISTRICT HOSPITAL' => 'SAN VICENTE',
        'SAN VICENTE MUNICIPAL HEALTH OFFICE' => 'SAN VICENTE',
        'TAYTAY MUNICIPAL HEALTH OFFICE' => 'TAYTAY',
    ];
    $autoMunicipality = $facilityMunicipalityMap[$assignedFacility] ?? '';
}

// Determine cluster based on facility name keywords
$autoCluster = '';
if ($hasAssignedFacility) {
    $facilityUpper = strtoupper($assignedFacility);
    $clusterMap = [
        'REDCATS'      => ['ROXAS', 'EL NIDO', 'DUMARAN', 'CAGAYANCILLO', 'ARACELI', 'TAYTAY', 'SAN VICENTE'],
        'CAM'          => ['CUYO', 'AGUTAYA', 'MAGSAYSAY'],
        'BCCL'         => ['BUSUANGA', 'CORON', 'CULION', 'LINAPACAN'],
        'NABBrRBEQ-K'  => ['NARRA', 'ABORLAN', 'BROOKE', 'RIZAL', 'BATARAZA', 'BALABAC', 'SOFRONIO', 'ESPAÑOLA', 'QUEZON', 'KALAYAAN'],
    ];
    foreach ($clusterMap as $cluster => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($facilityUpper, $keyword) !== false) {
                $autoCluster = $cluster;
                break 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHO CONSO HFDP - Add New Record</title>
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
            <a href="index.php" class="sidebar-link">
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
                    <a href="index.php" class="admin-header-home">
                        <div class="admin-logo-placeholder" aria-hidden="true">
                            <img src="assets/images/pho-logo.png" alt="" class="admin-logo-img" onerror="this.style.display='none';this.parentElement.classList.add('no-img')">
                            <span class="admin-logo-fallback">PHO</span>
                        </div>
                        <div class="admin-header-title">
                            <h1>PHO CONSO HFDP — Add New Record</h1>
                            <span class="admin-header-subtitle">Provincial Health Office · Province of Palawan</span>
                        </div>
                    </a>
                </div>
                <div class="admin-header-right">
                    <div class="admin-header-meta">
                        <div class="admin-clock">
                            <div class="clock-date" id="clock-date">-- -- ----</div>
                            <div class="clock-time" id="clock-time">--:--:--</div>
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

        <div class="form-section">
            <form id="record-form" class="data-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="year">Year <span class="required">*</span></label>
                        <select id="year" name="year" required data-sd-no-other>
                            <option value="">Select Year</option>
                            <?php for ($year = 2024; $year <= 2100; $year++): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cluster">Cluster <span class="required">*</span></label>
                        <?php if ($hasAssignedFacility && !empty($autoCluster)): ?>
                            <input type="text" id="cluster" name="cluster" value="<?php echo htmlspecialchars($autoCluster); ?>" readonly class="readonly-field" required>
                        <?php else: ?>
                            <select id="cluster" name="cluster" required data-sd-no-other>
                                <option value="">Select Cluster</option>
                                <option value="REDCATS">REDCATS</option>
                                <option value="BCCL">BCCL</option>
                                <option value="CAM">CAM</option>
                                <option value="NABBrRBEQ-K">NABBrRBEQ-K</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <?php if ($hasAssignedFacility): ?>
                    <div class="form-group">
                        <label for="concerned_office_facility">Concerned Office / Facility <span class="required">*</span></label>
                        <input type="text" id="concerned_office_facility" name="concerned_office_facility" value="<?php echo htmlspecialchars($assignedFacility); ?>" readonly class="readonly-field" required>
                    </div>
                    <div class="form-group">
                        <label for="municipality">Municipality</label>
                        <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($autoMunicipality); ?>" readonly class="readonly-field">
                    </div>
                    <?php else: ?>
                    <div class="form-group full-width">
                        <label for="concerned_office_facility">Concerned Office / Facility <span class="required">*</span></label>
                        <select id="concerned_office_facility" name="concerned_office_facility" required>
                            <option value="">Select Concerned Office</option>
                            <option value="ABORLAN MEDICARE HOSPITAL">ABORLAN MEDICARE HOSPITAL</option>
                            <option value="ABORLAN MUNICIPAL HEALTH OFFICE">ABORLAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="ARACELI-DUMARAN DISTRICT HOSPITAL">ARACELI-DUMARAN DISTRICT HOSPITAL</option>
                            <option value="BALABAC DISTRICT HOSPITAL">BALABAC DISTRICT HOSPITAL</option>
                            <option value="BALABAC MUNICIPAL HEALTH OFFICE">BALABAC MUNICIPAL HEALTH OFFICE</option>
                            <option value="BATARAZA DISTRICT HOSPITAL">BATARAZA DISTRICT HOSPITAL</option>
                            <option value="BATARAZA MUNICIPAL HEALTH OFFICE">BATARAZA MUNICIPAL HEALTH OFFICE</option>
                            <option value="BATARAZA MUNICIPAL HOSPITAL">BATARAZA MUNICIPAL HOSPITAL</option>
                            <option value="BROOKES POINT MUNICIPAL HEALTH OFFICE">BROOKES POINT MUNICIPAL HEALTH OFFICE</option>
                            <option value="BROOKES POINT MUNICIPAL HOSPITAL">BROOKES POINT MUNICIPAL HOSPITAL</option>
                            <option value="BUSUANGA HEALTH OFFICE">BUSUANGA HEALTH OFFICE</option>
                            <option value="CORON DISTRICT HOSPITAL">CORON DISTRICT HOSPITAL</option>
                            <option value="CORON MUNICIPAL HEALTH OFFICE">CORON MUNICIPAL HEALTH OFFICE</option>
                            <option value="CULION MUNICIPAL HEALTH OFFICE">CULION MUNICIPAL HEALTH OFFICE</option>
                            <option value="CUYO DISTRICT HOSPITAL">CUYO DISTRICT HOSPITAL</option>
                            <option value="DR JOSE RIZAL DISTRICT HOSPITAL">DR JOSE RIZAL DISTRICT HOSPITAL</option>
                            <option value="EL NIDO COMMUNITY HOSPITAL">EL NIDO COMMUNITY HOSPITAL</option>
                            <option value="KALAYAAN MUNICIPAL HEALTH OFFICE">KALAYAAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="LINAPACAN MUNICIPAL HEALTH OFFICE">LINAPACAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="MUNICIPALITY OF AGUTAYA">MUNICIPALITY OF AGUTAYA</option>
                            <option value="MUNICIPALITY OF ARACELI">MUNICIPALITY OF ARACELI</option>
                            <option value="MUNICIPALITY OF CAGAYANCILLO">MUNICIPALITY OF CAGAYANCILLO</option>
                            <option value="MUNICIPALITY OF DUMARAN">MUNICIPALITY OF DUMARAN</option>
                            <option value="MUNICIPALITY OF EL NIDO">MUNICIPALITY OF EL NIDO</option>
                            <option value="MUNICIPALITY OF MAGSAYSAY">MUNICIPALITY OF MAGSAYSAY</option>
                            <option value="MUNICIPALITY OF ROXAS">MUNICIPALITY OF ROXAS</option>
                            <option value="MUNICIPALITY OF SAN VICENTE">MUNICIPALITY OF SAN VICENTE</option>
                            <option value="MUNICIPALITY OF TAYTAY">MUNICIPALITY OF TAYTAY</option>
                            <option value="NARRA MUNICIPAL HEALTH OFFICE">NARRA MUNICIPAL HEALTH OFFICE</option>
                            <option value="NARRA MUNICIPAL HOSPITAL">NARRA MUNICIPAL HOSPITAL</option>
                            <option value="NORTHERN PALAWAN PROVINCIAL HOSPITAL">NORTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                            <option value="QUEZON MEDICARE HOSPITAL">QUEZON MEDICARE HOSPITAL</option>
                            <option value="QUEZON MUNICIPAL HEALTH OFFICE">QUEZON MUNICIPAL HEALTH OFFICE</option>
                            <option value="RIZAL DISTRICT HOSPITAL">RIZAL DISTRICT HOSPITAL</option>
                            <option value="RIZAL MUNICIPAL HEALTH OFFICE">RIZAL MUNICIPAL HEALTH OFFICE</option>
                            <option value="ROXAS MEDICARE HOSPITAL">ROXAS MEDICARE HOSPITAL</option>
                            <option value="SAN VICENTE DISTRICT HOSPITAL">SAN VICENTE DISTRICT HOSPITAL</option>
                            <option value="SOFRONIO ESPAÑOLA DISTRICT HOSPITAL">SOFRONIO ESPAÑOLA DISTRICT HOSPITAL</option>
                            <option value="SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE">SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE</option>
                            <option value="SOUTHERN PALAWAN PROVINCIAL HOSPITAL">SOUTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="facility_level">BHS/PCF/HOSP (Facility Level) <span class="required">*</span></label>
                        <?php if ($hasAssignedFacility && $isHospital): ?>
                            <input type="text" id="facility_level" name="facility_level" value="HOSP" readonly class="readonly-field" required>
                        <?php elseif ($hasAssignedFacility && !$isHospital): ?>
                            <select id="facility_level" name="facility_level" required data-sd-no-other>
                                <option value="">Select Facility Level</option>
                                <option value="BHS">BHS (Barangay Health Station)</option>
                                <option value="PCF">PCF (Primary Care Facility)</option>
                            </select>
                        <?php else: ?>
                            <select id="facility_level" name="facility_level" required data-sd-no-other>
                                <option value="">Select Facility Level</option>
                                <option value="BHS">BHS (Barangay Health Station)</option>
                                <option value="PCF">PCF (Primary Care Facility)</option>
                                <option value="HOSP">HOSP (Hospital)</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div><!-- /form-grid (header fields) -->

                <!-- ═══ ITEM REPEATER ═══ -->
                <div class="repeater-section">
                    <div class="repeater-header">
                        <span class="repeater-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            Items / Resources
                        </span>
                        <span class="repeater-count" id="repeater-count">1 item</span>
                    </div>
                    <div id="repeater-container">
                        <div class="repeater-row" data-row-index="0">
                            <div class="repeater-col rc-category">
                                <label>Category <span class="required">*</span></label>
                                <select class="r-category" name="category[]" required data-sd-no-other>
                                    <option value="">Select Category</option>
                                    <option value="INFRASTRUCTURE">INFRASTRUCTURE</option>
                                    <option value="EQUIPMENT">EQUIPMENT</option>
                                    <option value="HUMAN RESOURCE">HUMAN RESOURCE</option>
                                </select>
                            </div>
                            <div class="repeater-col rc-type">
                                <label>Requested Item/HR <span class="required">*</span></label>
                                <select class="r-type" name="type_of_health_facility[]" required data-sd-no-other>
                                    <option value="">— Select Category first —</option>
                                </select>
                                <input type="text" class="r-specify specify-input" name="type_specify[]" placeholder="Please specify..." style="display:none;margin-top:4px" oninput="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="repeater-col rc-units">
                                <label>Units</label>
                                <input type="number" class="r-units" name="number_of_units[]" min="0" value="0" required>
                            </div>
                            <div class="repeater-col rc-costing">
                                <label>Costing</label>
                                <input type="text" class="r-costing" name="costing[]" value="0.00" required>
                                <div class="number-to-words r-costing-words"></div>
                            </div>
                            <div class="repeater-col rc-fund">
                                <label>Fund Source <span class="required">*</span></label>
                                <select class="r-fund" name="fund_source[]" required>
                                    <option value="">Select</option>
                                    <option value="PLGU">PLGU</option>
                                    <option value="MLGU">MLGU</option>
                                    <option value="DOH">DOH</option>
                                    <option value="DPWH">DPWH</option>
                                    <option value="NGO">NGO</option>
                                </select>
                            </div>
                            <div class="repeater-col rc-actions">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-remove-row" title="Remove this item" style="visibility:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item-btn" class="btn btn-add-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Another Item
                    </button>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="presence_in_existing_plans">Presence in Existing Plans</label>
                        <select id="presence_in_existing_plans" name="presence_in_existing_plans" required data-sd-no-other>
                            <option value="">Select Option</option>
                            <option value="LIPH">LIPH</option>
                            <option value="LIDP">LIDP</option>
                            <option value="AIP">AIP</option>
                            <option value="CDP">CDP</option>
                            <option value="DTP">DTP</option>
                            <option value="AOP">AOP</option>
                            <option value="NONE">NONE</option>
                        </select>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Cancel</button>
                </div>
            </form>
        </div>

        <div id="message" class="message"></div>

        <!-- Live Preview Section -->
        <div class="live-preview-section">
            <div class="live-preview-header">
                <div class="live-preview-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Live Preview
                </div>
                <span class="live-preview-badge" id="lp-status">Waiting for input...</span>
            </div>
            <div class="live-preview-body">
                <table class="live-preview-table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="lp-label">Year</td><td class="lp-value" id="lp-year">&mdash;</td></tr>
                        <tr><td class="lp-label">Cluster</td><td class="lp-value" id="lp-cluster">&mdash;</td></tr>
                        <tr><td class="lp-label">Concerned Office / Facility</td><td class="lp-value" id="lp-concerned_office_facility">&mdash;</td></tr>
                        <tr><td class="lp-label">Municipality</td><td class="lp-value" id="lp-municipality">&mdash;</td></tr>
                        <tr><td class="lp-label">Facility Level</td><td class="lp-value" id="lp-facility_level">&mdash;</td></tr>
                        <tr><td class="lp-label" colspan="2" style="font-weight:700;padding-top:12px;border-bottom:2px solid var(--green-mid)">Items / Resources</td></tr>
                        <tr><td class="lp-value" colspan="2" id="lp-items" style="padding:0">&mdash;</td></tr>
                        <tr><td class="lp-label">Presence in Existing Plans</td><td class="lp-value" id="lp-presence_in_existing_plans">&mdash;</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script src="assets/js/searchable-dropdown.js?v=20260220"></script>
    <script src="assets/js/form.js?v=20260220"></script>
    <script src="assets/js/clock.js?v=20260220"></script>
    <script src="assets/js/admin-menu.js?v=20260220"></script>
    <script src="assets/js/sidebar.js?v=20260220"></script>
    <script src="assets/js/preloader.js?v=20260220"></script>
    <script>
        /* ═══ Cascading data ═══ */
        var CATEGORY_TYPE_MAP = {
            'INFRASTRUCTURE': ['SPECIFY WHAT TO CONSTRUCT'],
            'EQUIPMENT': [
                'MEDICAL EQUIPMENT', 'LABORATORY EQUIPMENT', 'OTHER EQUIPMENT'
            ],
            'HUMAN RESOURCE': [
                'MEDICAL OFFICER', 'MEDICAL SPECIALIST', 'NURSE', 'NURSING ATTENDANT',
                'MIDWIFE', 'RADIOLOGIC TECHNOLOGIST', 'PHARMACIST', 'DENTIST',
                'DENTAL AIDE', 'MEDICAL TECHNOLOGIST', 'MEDICAL LABORATORY TECHNICIAN',
                'LABORATORY ASSISTANT', 'ADMINISTRATIVE OFFICER', 'ADMINISTRATIVE ASSISTANT',
                'ADMINISTRATIVE AIDE (UTILITY)', 'CIVIL ENGINEER', 'SANITARY ENGINEER',
                'SANITATION INSPECTOR', 'NUTRITIONIST-DIETITIAN',
                'HEALTH EDUCATION & PROMOTIONS OFFICER', 'STATISTICIAN', 'OTHERS'
            ]
        };
        var SPECIFY_TRIGGERS = ['SPECIFY WHAT TO CONSTRUCT', 'MEDICAL EQUIPMENT', 'LABORATORY EQUIPMENT', 'OTHER EQUIPMENT', 'OTHERS'];

        /* ═══ Per-row cascading helpers ═══ */
        function populateTypeForRow(row) {
            var sel = row.querySelector('.r-type');
            var catSel = row.querySelector('.r-category');
            if (!sel || !catSel) return;
            var catVal = catSel.value;
            sel.innerHTML = '';
            var opts = CATEGORY_TYPE_MAP[catVal] || [];
            if (!catVal) {
                sel.innerHTML = '<option value="">\u2014 Select Category first \u2014</option>';
                sel.disabled = true;
            } else if (opts.length === 0) {
                sel.innerHTML = '<option value="">N/A for this category</option>';
                sel.disabled = true;
            } else {
                sel.disabled = false;
                sel.innerHTML = '<option value="">Select Type</option>';
                opts.forEach(function(o) {
                    var opt = document.createElement('option');
                    opt.value = o; opt.textContent = o;
                    sel.appendChild(opt);
                });
            }
            /* Re-sync SearchableDropdown UI — destroy old & create fresh */
            if (typeof reinitSearchableDropdown === 'function') {
                reinitSearchableDropdown(sel);
            } else if (typeof refreshSearchableDropdownEl === 'function') {
                refreshSearchableDropdownEl(sel);
            }
            toggleSpecifyForRow(row);
        }

        function toggleSpecifyForRow(row) {
            var sel = row.querySelector('.r-type');
            var spec = row.querySelector('.r-specify');
            if (!sel || !spec) return;
            if (SPECIFY_TRIGGERS.indexOf(sel.value) !== -1) {
                spec.style.display = 'block'; spec.required = true;
            } else {
                spec.style.display = 'none'; spec.required = false; spec.value = '';
            }
        }

        /* ═══ Repeater management ═══ */
        var repeaterIdx = 1;

        function addRepeaterRow() {
            var container = document.getElementById('repeater-container');
            var firstRow = container.querySelector('.repeater-row');
            var newRow = firstRow.cloneNode(true);
            newRow.setAttribute('data-row-index', repeaterIdx++);

            /* Strip searchable-dropdown artifacts from cloned row:
               The native <select> is a SIBLING of .sd-wrap (not inside it),
               so we must reset the select's SD flags separately. */
            newRow.querySelectorAll('select[data-sd-init]').forEach(function(s) {
                delete s.dataset.sdInit;
                s.style.display = '';
                s.removeAttribute('tabindex');
                s.removeAttribute('aria-hidden');
            });
            newRow.querySelectorAll('.sd-wrap').forEach(function(w) { w.remove(); });

            /* Reset values */
            newRow.querySelectorAll('select').forEach(function(s) { s.selectedIndex = 0; s.disabled = false; });
            newRow.querySelectorAll('input[type="number"]').forEach(function(i) { i.value = 0; });
            newRow.querySelectorAll('.r-costing').forEach(function(i) { i.value = '0.00'; });
            newRow.querySelectorAll('.r-specify').forEach(function(i) { i.style.display = 'none'; i.required = false; i.value = ''; });
            newRow.querySelectorAll('.r-costing-words').forEach(function(w) { w.textContent = ''; });
            var tSel = newRow.querySelector('.r-type');
            if (tSel) { tSel.innerHTML = '<option value="">\u2014 Select Category first \u2014</option>'; tSel.disabled = true; }

            /* Show remove button */
            var rmBtn = newRow.querySelector('.btn-remove-row');
            if (rmBtn) rmBtn.style.visibility = 'visible';

            container.appendChild(newRow);

            /* Init searchable dropdowns on new row */
            newRow.classList.add('sd-init-pending');
            if (typeof initSearchableDropdowns === 'function') initSearchableDropdowns('.sd-init-pending select');
            newRow.classList.remove('sd-init-pending');

            updateRepeaterUI();
            updatePreview();
        }

        function removeRepeaterRow(btn) {
            var row = btn.closest('.repeater-row');
            if (row) row.remove();
            updateRepeaterUI();
            updatePreview();
        }

        function updateRepeaterUI() {
            var rows = document.querySelectorAll('#repeater-container .repeater-row');
            /* Count badge */
            var el = document.getElementById('repeater-count');
            if (el) el.textContent = rows.length + (rows.length === 1 ? ' item' : ' items');
            /* Show/hide remove buttons */
            rows.forEach(function(r) {
                var b = r.querySelector('.btn-remove-row');
                if (b) b.style.visibility = (rows.length === 1) ? 'hidden' : 'visible';
            });
        }

        /* ═══ Per-row costing helpers ═══ */
        function handleRowCostingInput(input, row) {
            var value = input.value.replace(/[^0-9.]/g, '');
            var parts = value.split('.');
            if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
            if (parts[1] && parts[1].length > 2) value = parts[0] + '.' + parts[1].substring(0, 2);
            if (input.value !== value) input.value = value;
            var w = row.querySelector('.r-costing-words');
            if (w) { var n = parseFloat(value) || 0; w.textContent = n > 0 ? numberToWords(n) + ' Pesos' : ''; }
        }

        function formatRowCostingOnBlur(input, row) {
            var val = input.value.trim();
            if (!val) input.value = '0.00';
            else { var n = parseFloat(val) || 0; input.value = n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
            var w = row.querySelector('.r-costing-words');
            if (w) { var n2 = parseFloat(input.value.replace(/,/g, '')) || 0; w.textContent = n2 > 0 ? numberToWords(n2) + ' Pesos' : ''; }
        }

        /* ═══ DOMContentLoaded ═══ */
        document.addEventListener('DOMContentLoaded', function() {
            var container = document.getElementById('repeater-container');

            /* ---- Event delegation on repeater ---- */
            container.addEventListener('change', function(e) {
                var row = e.target.closest('.repeater-row');
                if (!row) return;
                if (e.target.classList.contains('r-category')) { populateTypeForRow(row); updatePreview(); }
                if (e.target.classList.contains('r-type'))     { toggleSpecifyForRow(row); updatePreview(); }
                if (e.target.classList.contains('r-fund'))     { updatePreview(); }
            });

            container.addEventListener('input', function(e) {
                var row = e.target.closest('.repeater-row');
                if (!row) return;
                if (e.target.classList.contains('r-costing')) { handleRowCostingInput(e.target, row); }
                updatePreview();
            });

            container.addEventListener('focusout', function(e) {
                if (e.target.classList.contains('r-costing')) {
                    var row = e.target.closest('.repeater-row');
                    if (row) formatRowCostingOnBlur(e.target, row);
                }
            });

            container.addEventListener('click', function(e) {
                var rmBtn = e.target.closest('.btn-remove-row');
                if (rmBtn) removeRepeaterRow(rmBtn);
            });

            document.getElementById('add-item-btn').addEventListener('click', addRepeaterRow);

            /* ---- Searchable dropdowns ---- */
            if (typeof initSearchableDropdowns === 'function') initSearchableDropdowns('#record-form select');

            /* ---- Live Preview ---- */
            var lpHeaderFields = ['year', 'cluster', 'concerned_office_facility', 'municipality', 'facility_level', 'presence_in_existing_plans'];
            var lpStatus = document.getElementById('lp-status');

            function getFieldValue(id) {
                var el = document.getElementById(id);
                if (!el) return '';
                if (el.tagName === 'SELECT') return el.options[el.selectedIndex] ? el.options[el.selectedIndex].text.trim() : '';
                return el.value.trim();
            }

            function fmtCost(val) {
                if (!val || val === '0.00' || val === '0') return '';
                var num = parseFloat(val.replace(/,/g, ''));
                if (isNaN(num)) return val;
                return '\u20B1' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            window.updatePreview = function updatePreview() {
                var filled = 0, total = lpHeaderFields.length;

                /* Header fields */
                lpHeaderFields.forEach(function(id) {
                    var cell = document.getElementById('lp-' + id);
                    if (!cell) return;
                    var val = getFieldValue(id);
                    if (!val || val.startsWith('Select ') || val.startsWith('\u2014')) {
                        cell.textContent = '\u2014'; cell.className = 'lp-value lp-empty';
                    } else {
                        cell.textContent = val; cell.className = 'lp-value lp-filled'; filled++;
                    }
                });

                /* Items */
                var itemsCell = document.getElementById('lp-items');
                if (itemsCell) {
                    var rows = document.querySelectorAll('#repeater-container .repeater-row');
                    var html = '<table class="lp-items-table"><thead><tr><th>#</th><th>Category</th><th>Item/HR</th><th>Units</th><th>Costing</th><th>Fund</th></tr></thead><tbody>';
                    rows.forEach(function(row, i) {
                        var cS = row.querySelector('.r-category'), tS = row.querySelector('.r-type'),
                            sp = row.querySelector('.r-specify'), uI = row.querySelector('.r-units'),
                            co = row.querySelector('.r-costing'), fS = row.querySelector('.r-fund');
                        var cat = cS && cS.value ? cS.options[cS.selectedIndex].text : '';
                        var typ = '';
                        if (sp && sp.style.display !== 'none' && sp.value.trim()) typ = sp.value.trim();
                        else if (tS && tS.value) typ = tS.options[tS.selectedIndex].text;
                        var units = uI ? uI.value : '0';
                        var cost = co ? fmtCost(co.value) : '';
                        var fund = fS && fS.value ? fS.options[fS.selectedIndex].text : '';
                        if (cat.startsWith('Select') || cat.startsWith('\u2014')) cat = '';
                        if (typ.startsWith('Select') || typ.startsWith('\u2014') || typ === 'N/A for this category') typ = '';
                        if (fund.startsWith('Select')) fund = '';
                        total++;
                        if (cat && typ) filled++;
                        html += '<tr><td>' + (i+1) + '</td><td>' + (cat||'\u2014') + '</td><td>' + (typ||'\u2014') + '</td><td>' + units + '</td><td>' + (cost||'\u2014') + '</td><td>' + (fund||'\u2014') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                    itemsCell.innerHTML = html;
                }

                /* Status badge */
                if (lpStatus) {
                    if (filled === 0) { lpStatus.textContent = 'Waiting for input...'; lpStatus.className = 'live-preview-badge lp-badge-waiting'; }
                    else if (filled >= total) { lpStatus.textContent = 'All fields filled \u2714'; lpStatus.className = 'live-preview-badge lp-badge-complete'; }
                    else { lpStatus.textContent = filled + ' of ' + total + ' fields filled'; lpStatus.className = 'live-preview-badge lp-badge-partial'; }
                }
            };

            /* Bind header field listeners */
            lpHeaderFields.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) { el.addEventListener('input', updatePreview); el.addEventListener('change', updatePreview); }
            });

            /* MutationObserver for searchable-dropdown mutations */
            var formEl = document.getElementById('record-form');
            if (formEl && typeof MutationObserver !== 'undefined') {
                var mo = new MutationObserver(updatePreview);
                formEl.querySelectorAll('select').forEach(function(s) {
                    mo.observe(s, { attributes: true, attributeFilter: ['value'], childList: true, subtree: true });
                });
            }

            updatePreview();
        });
    </script>
</body>
</html>
