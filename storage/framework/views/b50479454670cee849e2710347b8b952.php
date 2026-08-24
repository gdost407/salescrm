<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title><?php echo e($title ?? config('app.name', 'Laravel')); ?></title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php echo app('flux')->fluxAppearance(); ?>


<meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Dashboard - Analytics | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

    <meta name="description" content="" />

    <style>
      #page-loading-progress {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 2000;
        width: 0;
        height: 3px;
        pointer-events: none;
        background: #696cff;
        box-shadow: 0 0 8px rgb(105 108 255 / 55%);
        opacity: 0;
        transition: opacity 150ms ease;
      }

      #page-loading-progress.is-loading {
        width: 85%;
        opacity: 1;
        animation: page-loading-progress 1.2s ease-in-out infinite;
      }

      #page-loading-progress.is-complete {
        width: 100%;
        opacity: 1;
        transition: width 180ms ease, opacity 250ms ease 180ms;
      }

      @keyframes page-loading-progress {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(120%); }
      }
    </style>

    <script>
      (() => {
        const progress = document.createElement('div');
        progress.id = 'page-loading-progress';

        const start = () => {
          progress.classList.remove('is-complete');
          progress.classList.add('is-loading');
        };

        const finish = () => {
          progress.classList.remove('is-loading');
          progress.classList.add('is-complete');
          window.setTimeout(() => progress.classList.remove('is-complete'), 450);
        };

        document.addEventListener('DOMContentLoaded', () => {
          document.body.prepend(progress);

          document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');

            if (link && !event.defaultPrevented && link.target !== '_blank' && !link.hasAttribute('download') && link.origin === window.location.origin) {
              start();
            }
          });

          document.addEventListener('submit', (event) => {
            if (!event.defaultPrevented) {
              start();
            }
          });
        });

        window.addEventListener('beforeunload', start);
        document.addEventListener('livewire:navigating', start);
        document.addEventListener('livewire:navigated', finish);
        document.addEventListener('livewire:init', () => {
          Livewire.hook('request', ({ succeed, fail }) => {
            start();
            succeed(() => finish());
            fail(() => finish());
          });
        });
      })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('assets/img/favicon/favicon.ico')); ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/fonts/boxicons.css')); ?>" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/css/core.css')); ?>" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/css/theme-default.css')); ?>" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/demo.css')); ?>" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')); ?>" />

    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/libs/apex-charts/apex-charts.css')); ?>" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="<?php echo e(asset('assets/vendor/js/helpers.js')); ?>"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?php echo e(asset('assets/js/config.js')); ?>"></script><?php /**PATH P:\xampp\htdocs\GDost\salescrm\resources\views/partials/head.blade.php ENDPATH**/ ?>