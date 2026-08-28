<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php echo $__env->make('partials.aside', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="layout-page">
        <?php echo $__env->make('partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="content-wrapper">
          <?php echo $__env->yieldContent('content'); ?>
          <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <div id="app-notifications" class="position-fixed top-0 end-0 p-3" style="z-index: 1080; width: min(380px, 100%);"></div>
  <script>
    (() => {
      const shown = new Set();
      const container = document.getElementById('app-notifications');
      const csrf = document.querySelector('meta[name="csrf-token"]').content;

      const showNotification = (notification) => {
        if (shown.has(notification.id)) return;
        shown.add(notification.id);
        const toast = document.createElement('div');
        toast.className = 'alert alert-warning shadow-sm mb-3';
        const title = document.createElement('strong');
        title.textContent = notification.title;
        const message = document.createElement('div');
        message.textContent = notification.message;
        toast.append(title, message);
        if (notification.url) toast.style.cursor = 'pointer';
        toast.addEventListener('click', () => {
          if (notification.url) window.location.href = notification.url;
        });
        container.append(toast);
        window.setTimeout(() => toast.remove(), 12000);

        if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission();
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification(notification.title, { body: notification.message });
        }
        fetch(`<?php echo e(url('/notifications')); ?>/${notification.id}/read`, {
          method: 'PATCH',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
      };

      const poll = async () => {
        const response = await fetch('<?php echo e(route('notifications.unread')); ?>', { headers: { 'Accept': 'application/json' } });
        if (response.ok) (await response.json()).notifications.forEach(showNotification);
      };

      poll().catch(() => {});
      window.setInterval(() => poll().catch(() => {}), 30000);
    })();
  </script>
  <?php echo $__env->make('partials.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH P:\xampp\htdocs\GDost\salescrm\resources\views/layouts/app.blade.php ENDPATH**/ ?>