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
    <div class="container">
        <header class="unified-header">
            <div class="header-left">
                <div class="header-pho-logo-wrap">
                    <img src="assets/images/pho-logo.png" alt="Provincial Health Office of Palawan" class="header-pho-logo" onerror="this.parentElement.style.display='none'">
                </div>
                <div class="header-title-section">
                    <h1>PHO CONSO HFDP — Add New Record</h1>
                    <span class="header-subtitle">Provincial Health Office · Province of Palawan</span>
                </div>
            </div>
            <div class="header-right">
                <div class="header-clock">
                    <div class="clock-time" id="clock-time">--:--:--</div>
                    <div class="clock-date" id="clock-date">-- -- ----</div>
                </div>
                <nav>
                    <a href="index.php">Dashboard</a>
                    <a href="form.php" class="active">Add New Record</a>
                </nav>
            </div>
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
                        <select id="type_of_health_facility" name="type_of_health_facility" required>
                            <option value="">Select Type</option>
                            <optgroup label="A–C">
                                <option value="ADMINISTRATIVE AIDE I">ADMINISTRATIVE AIDE I</option>
                                <option value="(ADMINISTRATIVE AIDE I)"> (ADMINISTRATIVE AIDE I)</option>
                                <option value="ACCOUNTANT I">ACCOUNTANT I</option>
                                <option value="ADMIN AIDE I">ADMIN AIDE I</option>
                                <option value="ADMIN ASSISTANT">ADMIN ASSISTANT</option>
                                <option value="ADMIN ASSISTANT II">ADMIN ASSISTANT II</option>
                                <option value="ADMIN OFFICER">ADMIN OFFICER</option>
                                <option value="ADMIN OFFICER IV">ADMIN OFFICER IV</option>
                                <option value="ADMIN OFFICER V">ADMIN OFFICER V</option>
                                <option value="ADMINISTRATIVE AIDE I">ADMINISTRATIVE AIDE I</option>
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

                    <div class="form-group">
                        <label for="number_of_units">Number of Units</label>
                        <input type="number" id="number_of_units" name="number_of_units" min="0" value="0" required>
                    </div>

                    <div class="form-group">
                        <label for="target">Target <span class="required">*</span></label>
                        <select id="target" name="target" required>
                            <option value="">Select Target</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="costing">Costing</label>
                        <input type="number" id="costing" name="costing" step="0.01" min="0" value="0.00" required>
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
                        <select id="presence_in_existing_plans" name="presence_in_existing_plans" required>
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

    <script src="assets/js/target-options.js"></script>
    <script src="assets/js/form.js"></script>
    <script src="assets/js/clock.js"></script>
    <script>
        // Populate Target dropdown on page load
        document.addEventListener('DOMContentLoaded', function() {
            const targetSelect = document.getElementById('target');
            if (targetSelect) {
                targetSelect.innerHTML = generateTargetOptions('');
            }
        });
    </script>
</body>
</html>
