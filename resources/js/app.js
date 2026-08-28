async function copyTextToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.warn('navigator.clipboard failed, trying fallback:', err);
        }
    }

    // Fallback for non-secure contexts (e.g. HTTP) or unsupported browsers
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-999999px';
    textarea.style.top = '-999999px';
    textarea.setAttribute('readonly', '');
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, 99999);

    let success = false;
    try {
        success = document.execCommand('copy');
    } catch (err) {
        console.error('execCommand copy failed:', err);
    }
    document.body.removeChild(textarea);
    return success;
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-api-token]');

    if (! button) {
        return;
    }

    const token = button.dataset.copyApiToken;

    if (! token) {
        return;
    }

    const copied = await copyTextToClipboard(token);

    if (copied) {
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-primary');

        window.setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }
});

