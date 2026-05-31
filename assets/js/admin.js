// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const sidebarToggle = document.getElementById('sidebarToggle');
  
  // Check if sidebar state is saved in localStorage
  const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  
  // Apply initial state
  if (isSidebarCollapsed) {
      sidebar.classList.add('collapsed');
      mainContent.classList.add('expanded');
  }
  
  // Toggle sidebar
  sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
      
      // Save state to localStorage
      localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
  });
  
  // Handle window resize
  window.addEventListener('resize', function() {
      if (window.innerWidth < 992) {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
      } else {
          // Restore previous state on larger screens
          if (localStorage.getItem('sidebarCollapsed') === 'true') {
              sidebar.classList.add('collapsed');
              mainContent.classList.add('expanded');
          } else {
              sidebar.classList.remove('collapsed');
              mainContent.classList.remove('expanded');
          }
      }
  });
  
  // Initialize sidebar state based on screen size
  if (window.innerWidth < 992) {
      sidebar.classList.add('collapsed');
      mainContent.classList.add('expanded');
  }
}); 