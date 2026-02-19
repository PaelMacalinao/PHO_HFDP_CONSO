/**
 * Role-Based Access Control (RBAC) JavaScript
 * Handles UI restrictions based on user role
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get user role from page data attribute or session
    const userRole = document.documentElement.getAttribute('data-user-role');
    
    if (userRole === 'staff') {
        // Disable facility filter for staff users
        const facilityFilterSelect = document.getElementById('filter-facility-level');
        if (facilityFilterSelect) {
            // Note: This is for facility_level, not concerned_office_facility
            // Staff can still filter by facility level, but they only see their assigned facility's records
        }
        
        // Disable concerned office/facility filter if it exists
        const concernedOfficeSelect = document.getElementById('filter-concerned_office_facility');
        if (concernedOfficeSelect) {
            concernedOfficeSelect.disabled = true;
            concernedOfficeSelect.title = 'You can only view records from your assigned facility';
        }
        
        // Show notification that user is viewing filtered data
        showStaffNotification();
    }
});

function showStaffNotification() {
    // Optional: Add a notice that data is filtered
    const noticeHTML = `
        <div class="rbac-notice" style="
            background: #e8f4f8;
            border-left: 4px solid #0288d1;
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #01579b;
        ">
            <strong>Note:</strong> You are viewing records from your assigned facility only.
        </div>
    `;
    
    // Insert notice after filters header if it exists
    const filtersSection = document.querySelector('.filters-section');
    if (filtersSection) {
        const headerRow = filtersSection.querySelector('.filters-header-row');
        if (headerRow) {
            const notice = document.createElement('div');
            notice.innerHTML = noticeHTML;
            headerRow.insertAdjacentHTML('afterend', notice.innerHTML);
        }
    }
}
