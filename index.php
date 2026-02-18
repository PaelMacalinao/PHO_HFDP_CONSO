<?php
/**
 * PHO CONSO HFDP Dashboard
 * Main dashboard page with filters and data table
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHO CONSO HFDP - Dashboard</title>
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
                    <h1>PHO CONSO HFDP Dashboard</h1>
                    <span class="subtitle">Provincial Health Office · Province of Palawan</span>
                </div>
            </div>
            <nav>
                <a href="index.php" class="active">Dashboard</a>
                <a href="form.php">Add New Record</a>
            </nav>
        </header>

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
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                                <option value="2028">2028</option>
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
                    </div>
                </div>
            </div>
        </div>

        <div class="data-section">
            <div class="section-header">
                <h2>Records</h2>
                <div class="record-count">
                    <span id="record-count">0</span> record(s) found
                </div>
            </div>

            <div class="table-container">
                <table id="records-table" class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Year</th>
                            <th>Cluster</th>
                            <th>Concerned Office/Facility</th>
                            <th>Facility Level</th>
                            <th>Category</th>
                            <th>Type of Health Facility</th>
                            <th>Number of Units</th>
                            <th>Facilities</th>
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
                            <td colspan="15" class="no-data">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
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

    <script src="assets/js/app.js"></script>
</body>
</html>
