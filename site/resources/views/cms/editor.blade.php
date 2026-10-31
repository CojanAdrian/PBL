{{-- Injected by App\Http\Middleware\InjectVisualEditor while an admin edits the site. --}}
<style>
    [data-cms-text] {
        outline: 1.5px dashed transparent;
        outline-offset: 4px;
        border-radius: 4px;
        cursor: text;
        transition: outline-color .15s, background-color .15s;
    }

    [data-cms-text]:hover { outline-color: rgb(37 99 235 / .65); background-color: rgb(37 99 235 / .06); }
    [data-cms-text][data-cms-missing] { outline-color: rgb(245 158 11 / .8); }
    /* While editing, show the text as it is stored: a heading styled in CAPITALS would otherwise be saved in capitals. */
    [data-cms-text].cms-active { outline: 2px solid #2563eb; background-color: rgb(37 99 235 / .08); white-space: pre-wrap; text-transform: none; }
    [data-cms-text].cms-saving { opacity: .55; }

    [data-cms-image] {
        cursor: pointer;
        outline: 2px dashed transparent;
        outline-offset: 4px;
        transition: outline-color .15s, filter .15s;
    }

    [data-cms-image]:hover { outline-color: rgb(37 99 235 / .75); filter: brightness(.92); }
    [data-cms-image].cms-saving { opacity: .55; }

    .cms-toast, .cms-badge {
        position: fixed;
        z-index: 2147483647;
        font: 600 13px/1.2 -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, sans-serif;
        color: #fff;
        background: rgb(11 15 25 / .88);
        backdrop-filter: blur(16px) saturate(180%);
        border-radius: 9999px;
        box-shadow: 0 10px 30px -10px rgb(0 0 0 / .5), inset 0 1px 0 rgb(255 255 255 / .12);
    }

    .cms-toast {
        left: 50%;
        bottom: 24px;
        padding: 10px 18px;
        transform: translate(-50%, 20px);
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s, transform .2s;
    }

    .cms-toast.cms-show { opacity: 1; transform: translate(-50%, 0); }
    .cms-toast.cms-error { background: rgb(190 18 60 / .92); }

    .cms-badge {
        left: 16px;
        bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        pointer-events: none;
    }

    .cms-badge::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 9999px;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgb(34 197 94 / .25);
    }
</style>

<div class="cms-badge">{{ __('Editing · click any outlined text or image') }}</div>
<div class="cms-toast" role="status" aria-live="polite"></div>

<script>
    (() => {
        const config = @json($config);
        const toastEl = document.querySelector('.cms-toast');
        let toastTimer;

        const toast = (message, error = false) => {
            toastEl.textContent = message;
            toastEl.classList.toggle('cms-error', error);
            toastEl.classList.add('cms-show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toastEl.classList.remove('cms-show'), 2200);
        };

        const send = async (url, body) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                body,
                credentials: 'same-origin',
            });

            if (! response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || @json(__('Could not save. Please try again.')));
            }

            return response.json();
        };

        const caretOffset = (el) => {
            const selection = window.getSelection();
            if (! selection.rangeCount) return 0;
            const range = selection.getRangeAt(0).cloneRange();
            range.selectNodeContents(el);
            range.setEnd(selection.focusNode, selection.focusOffset);
            return range.toString().length;
        };

        const moveCaret = (el, characters) => {
            window.getSelection().modify?.('move', characters < 0 ? 'backward' : 'forward', 'character');
        };

        const startEditing = (el) => {
            if (el.isContentEditable) return;

            // Read the text once cms-active has switched off any text-transform (see the style above).
            el.classList.add('cms-active');
            el.dataset.cmsOriginal = el.innerText;

            try { el.contentEditable = 'plaintext-only'; } catch { el.contentEditable = 'true'; }

            el.focus();

            const range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        };

        const stopEditing = async (el, save = true) => {
            // Read while white-space: pre-wrap still applies, so new lines survive.
            const original = el.dataset.cmsOriginal;
            const text = el.innerText.replace(/\n+$/, '');

            el.contentEditable = 'false';
            el.classList.remove('cms-active');
            el.innerText = save ? text : original;

            if (! save || text === original) {
                return;
            }

            const body = new FormData();
            body.append('key', el.dataset.cmsText);
            body.append('locale', config.locale);
            body.append('text', text);
            if (config.page) body.append('page', config.page);

            el.classList.add('cms-saving');

            try {
                await send(config.textUrl, body);

                // Same key used elsewhere on the page (e.g. header and footer).
                document.querySelectorAll(`[data-cms-text="${CSS.escape(el.dataset.cmsText)}"]`).forEach((other) => {
                    if (other !== el) other.innerText = text;
                    other.removeAttribute('data-cms-missing');
                });

                toast(@json(__('Saved')));
            } catch (error) {
                el.innerText = original;
                toast(error.message, true);
            } finally {
                el.classList.remove('cms-saving');
            }
        };

        const replaceImage = (img) => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/webp,image/gif,image/avif';

            input.addEventListener('change', async () => {
                if (! input.files.length) return;

                const body = new FormData();
                body.append('key', img.dataset.cmsImage);
                body.append('image', input.files[0]);
                if (config.page) body.append('page', config.page);

                img.classList.add('cms-saving');

                try {
                    const { url } = await send(config.imageUrl, body);
                    document.querySelectorAll(`[data-cms-image="${CSS.escape(img.dataset.cmsImage)}"]`).forEach((el) => {
                        el.src = url;
                        el.removeAttribute('srcset');
                    });
                    toast(@json(__('Image updated')));
                } catch (error) {
                    toast(error.message, true);
                } finally {
                    img.classList.remove('cms-saving');
                }
            });

            input.click();
        };

        document.addEventListener('click', (event) => {
            const text = event.target.closest('[data-cms-text]');

            if (text) {
                event.preventDefault();
                event.stopPropagation();
                startEditing(text);
                return;
            }

            const image = event.target.closest('[data-cms-image]');

            if (image) {
                event.preventDefault();
                event.stopPropagation();
                replaceImage(image);
                return;
            }

            // Stay in edit mode while browsing the site.
            const link = event.target.closest('a[href]');

            if (link && link.origin === location.origin && ! link.target) {
                event.preventDefault();
                const url = new URL(link.href);
                url.searchParams.set('cms_edit', '1');
                location.href = url.toString();
            }
        }, true);

        document.addEventListener('keydown', (event) => {
            const el = event.target.closest?.('[data-cms-text]');

            if (! el || ! el.isContentEditable) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                stopEditing(el, false);
            } else if (event.key === 'Enter' && event.shiftKey) {
                event.preventDefault();

                // A line break that still shows up at the very end of the text.
                const atEnd = el.innerText.replace(/\n$/, '').length <= caretOffset(el);

                if (! document.execCommand('insertText', false, atEnd ? '\n\n' : '\n')) {
                    document.execCommand('insertLineBreak');
                } else if (atEnd) {
                    moveCaret(el, -1);
                }
            } else if (event.key === 'Enter') {
                event.preventDefault();
                el.blur();
            }
        });

        document.addEventListener('focusout', (event) => {
            const el = event.target.closest?.('[data-cms-text]');
            if (el && el.isContentEditable) stopEditing(el);
        });

        // Don't send real form submissions (e.g. the contact form) while editing.
        document.addEventListener('submit', (event) => {
            event.preventDefault();
            toast(@json(__('Forms are turned off while editing')));
        }, true);

        window.parent?.postMessage({ type: 'cms:page', path: location.pathname, title: document.title }, location.origin);
    })();
</script>
