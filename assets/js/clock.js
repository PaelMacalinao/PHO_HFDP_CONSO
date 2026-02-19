/**
 * Real-time Clock and Date Display
 */

function updateClock() {
    const now = new Date();
    
    // 12-hour format with AM/PM
    let hours = now.getHours();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
    
    // Format date (Day, Month DD, YYYY)
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    
    const dayName = days[now.getDay()];
    const monthName = months[now.getMonth()];
    const day = now.getDate();
    const year = now.getFullYear();
    const dateString = `${dayName}, ${monthName} ${day}, ${year}`;
    
    // Update elements
    const timeElement = document.getElementById('clock-time');
    const dateElement = document.getElementById('clock-date');
    
    if (timeElement) {
        timeElement.textContent = timeString;
    }
    if (dateElement) {
        dateElement.textContent = dateString;
    }
}

// Initialize clock on page load
document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    // Update every second
    setInterval(updateClock, 1000);
});
