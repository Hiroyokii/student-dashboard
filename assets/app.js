(() => {
    const shell = document.createElement('dialog');
    shell.className = 'record-modal';
    shell.innerHTML = '<div class="modal-head"><h2></h2><button class="modal-close" type="button" aria-label="Close">×</button></div><div class="modal-body"></div>';
    document.body.appendChild(shell);
    const title = shell.querySelector('h2');
    const body = shell.querySelector('.modal-body');
    const close = () => shell.close();
    shell.querySelector('.modal-close').addEventListener('click', close);
    shell.addEventListener('click', event => { if (event.target === shell) close(); });

    document.addEventListener('click', async event => {
        const edit = event.target.closest('a.small-btn.edit');
        const del = event.target.closest('a.small-btn.delete');
        if (!edit && !del) return;
        if (edit && edit.dataset.nativeEdit === 'true') return;
        event.preventDefault();
        body.replaceChildren();
        if (edit) {
            title.textContent = 'Edit record';
            const response = await fetch(edit.href, { headers: { 'X-Requested-With': 'fetch' } });
            const markup = new DOMParser().parseFromString(await response.text(), 'text/html');
            const sourceForm = markup.querySelector('dialog .record-form, .card form');
            if (!sourceForm) return;
            const form = sourceForm.cloneNode(true);
            form.action = `${location.pathname}?id=${encodeURIComponent(new URL(edit.href).searchParams.get('id'))}`;
            const actions = form.querySelector('.form-actions');
            if (actions) {
                actions.querySelectorAll('a').forEach(link => link.remove());
                actions.classList.add('modal-actions');
            }
            body.append(form);
            shell.showModal();
            return;
        }
        title.textContent = 'Delete record?';
        const message = document.createElement('p'); message.textContent = 'This action cannot be undone. Are you sure you want to delete this record?';
        const actions = document.createElement('div'); actions.className = 'modal-actions';
        const cancel = document.createElement('button'); cancel.type = 'button'; cancel.className = 'btn btn-light'; cancel.textContent = 'Cancel'; cancel.addEventListener('click', close);
        const confirm = document.createElement('a'); confirm.className = 'btn btn-danger'; confirm.href = del.href; confirm.textContent = 'Delete';
        actions.append(cancel, confirm); body.append(message, actions); shell.showModal();
    });
})();
