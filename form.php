<?php
/**
 * PHO CONSO HFDP Data Entry Form
 * Form for adding new records
 */
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
    <div class="topbar">
        <div class="topbar-inner">
            <div class="topbar-left">
                <img src="assets/images/company-logo.png" alt="Company Logo" class="topbar-logo" onerror="this.style.display='none'">
                <div class="topbar-titles">
                    <div class="topbar-title">PHO CONSO HFDP</div>
                    <div class="topbar-subtitle">Health Facility Development Plan Encoding & Monitoring</div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <header>
            <div class="header-brand">
                <div class="header-logos">
                    <img src="assets/images/palawan-logo.png" alt="Province of Palawan" class="palawan-logo" onerror="this.style.display='none'">
                    <img src="assets/images/pho-logo.png" alt="Provincial Health Office of Palawan" class="pho-logo" onerror="this.style.display='none'">
                </div>
                <div class="header-title-wrap">
                    <h1>PHO CONSO HFDP — Add New Record</h1>
                    <span class="subtitle">Provincial Health Office · Province of Palawan</span>
                </div>
            </div>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="form.php" class="active">Add New Record</a>
            </nav>
        </header>

        <div class="form-section">
            <form id="record-form" class="data-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="year">Year <span class="required">*</span></label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cluster">Cluster <span class="required">*</span></label>
                        <select id="cluster" name="cluster" required>
                            <option value="">Select Cluster</option>
                            <option value="REDCATS">REDCATS</option>
                            <option value="BCCL">BCCL</option>
                            <option value="CAM">CAM</option>
                            <option value="NABBrRBEQ-K">NABBrRBEQ-K</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="concerned_office_facility">Concerned Office / Facility <span class="required">*</span></label>
                        <input type="text" id="concerned_office_facility" name="concerned_office_facility" required>
                    </div>

                    <div class="form-group">
                        <label for="facility_level">BHS/PCF/HOSP (Facility Level) <span class="required">*</span></label>
                        <select id="facility_level" name="facility_level" required>
                            <option value="">Select Facility Level</option>
                            <option value="BHS">BHS (Barangay Health Station)</option>
                            <option value="PCF">PCF (Primary Care Facility)</option>
                            <option value="HOSP">HOSP (Hospital)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">INFRA/EQUIP/HR (Category) <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="INFRASTRUCTURE">INFRASTRUCTURE</option>
                            <option value="EQUIPMENT">EQUIPMENT</option>
                            <option value="HUMAN RESOURCE">HUMAN RESOURCE</option>
                            <option value="TRANSPORTATION">TRANSPORTATION</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="type_of_health_facility">Type of Health Facility</label>
                        <input type="text" id="type_of_health_facility" name="type_of_health_facility">
                    </div>

                    <div class="form-group">
                        <label for="number_of_units">Number of Units</label>
                        <input type="number" id="number_of_units" name="number_of_units" min="0" value="0">
                    </div>

                    <div class="form-group full-width">
                        <label for="facilities">Facilities (Specific Item Description)</label>
                        <textarea id="facilities" name="facilities" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="target">Target</label>
                        <input type="text" id="target" name="target">
                    </div>

                    <div class="form-group">
                        <label for="costing">Costing</label>
                        <input type="number" id="costing" name="costing" step="0.01" min="0" value="0.00">
                    </div>

                    <div class="form-group full-width">
                        <label for="fund_source">Fund Source <span class="required">*</span></label>
                        <select id="fund_source" name="fund_source" required>
                            <option value="">Select Fund Source</option>
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

                    <div class="form-group">
                        <label for="presence_in_existing_plans">Presence in Existing Plans</label>
                        <select id="presence_in_existing_plans" name="presence_in_existing_plans">
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

                    <div class="form-group full-width">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="4"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Cancel</button>
                </div>
            </form>
        </div>

        <div id="message" class="message"></div>
    </div>

    <script src="assets/js/form.js"></script>
</body>
</html>
