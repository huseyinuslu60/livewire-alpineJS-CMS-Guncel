<!-- Real-time Dashboard Widget -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
        <h5 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-chart-line text-indigo-500 mr-2"></i>
            Canlı İstatistikler
        </h5>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Online Users -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="online-users">{{ $totalUsers ?? 0 }}</p>
                <p class="text-sm text-gray-600">Toplam Kullanıcı</p>
            </div>

            <!-- Today's Activity -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-calendar-day text-white text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="today-activity">{{ $todayRegistrations ?? 0 }}</p>
                <p class="text-sm text-gray-600">Bugünkü Kayıt</p>
            </div>

            <!-- System Status -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-server text-white text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="system-status">Online</p>
                <p class="text-sm text-gray-600">Sistem Durumu</p>
            </div>
        </div>

        <!-- Real-time Clock -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
                    <i class="fas fa-clock text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700" id="current-time"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Update Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('tr-TR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString('tr-TR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const clockElement = document.getElementById('current-time');
        if (clockElement) {
            clockElement.textContent = `${dateString} - ${timeString}`;
        }
    }
    
    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
    
    // Simulate real-time updates (in a real app, you'd use WebSockets or Server-Sent Events)
    function updateStats() {
        // This would typically fetch data from an API endpoint
        // For now, we'll just add some visual feedback
        const elements = document.querySelectorAll('[id$="-users"], [id$="-activity"], [id$="-status"]');
        elements.forEach(element => {
            element.style.transform = 'scale(1.05)';
            element.style.transition = 'transform 0.3s ease';
            setTimeout(() => {
                element.style.transform = 'scale(1)';
            }, 300);
        });
    }
    
    // Update stats every 30 seconds
    setInterval(updateStats, 30000);
    
    // Add loading animation
    function addLoadingAnimation() {
        const cards = document.querySelectorAll('.bg-white.rounded-2xl');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    addLoadingAnimation();
});
</script>
