/**
 * Open a URL in a background tab (Chromium/Brave) while keeping the app focused.
 *
 * `window.open('_blank')` opens a foreground tab by design. A programmatic
 * click on an anchor carrying a ctrl/meta modifier is read as "open in
 * background tab". Call this synchronously inside the user gesture.
 */
export function openInBackgroundTab(url: string): void {
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.target = '_blank';
    anchor.rel = 'noopener';
    anchor.style.display = 'none';
    document.body.appendChild(anchor);

    anchor.dispatchEvent(
        new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window,
            ctrlKey: true,
            metaKey: true,
        }),
    );

    anchor.remove();
}
