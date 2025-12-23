/**
 * Parallax Rocket Layout - Countdown Script
 */

(function() {
    'use strict';
    
    // Get countdown target from data attribute
    var countdownElement = document.querySelector('.countdown-timer');
    if (!countdownElement) {
        return;
    }
    
    var targetTime = parseInt(countdownElement.getAttribute('data-target-time')) * 1000;
    
    if (!targetTime || targetTime <= 0) {
        return;
    }
    
    /**
     * Update countdown display
     */
    function updateCountdown() {
        var now = new Date().getTime();
        var distance = targetTime - now;

        if (distance < 0) {
            document.getElementById('pr-days').textContent = '00';
            document.getElementById('pr-hours').textContent = '00';
            document.getElementById('pr-minutes').textContent = '00';
            document.getElementById('pr-seconds').textContent = '00';
            return;
        }

        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('pr-days').textContent = String(days).padStart(2, '0');
        document.getElementById('pr-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('pr-minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('pr-seconds').textContent = String(seconds).padStart(2, '0');
    }

    // Initial update
    updateCountdown();
    
    // Update every second
    setInterval(updateCountdown, 1000);
})();
