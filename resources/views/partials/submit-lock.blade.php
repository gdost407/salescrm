<script>
  (() => {
    const setFormSubmitState = (form, disabled) => {
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.disabled = disabled;
      });
    };

    window.unlockFormSubmit = (form) => setFormSubmitState(form, false);
    document.addEventListener('submit', (event) => {
      if (event.target instanceof HTMLFormElement) setFormSubmitState(event.target, true);
    }, true);
    window.addEventListener('pageshow', () => document.querySelectorAll('form').forEach((form) => setFormSubmitState(form, false)));
  })();
</script>