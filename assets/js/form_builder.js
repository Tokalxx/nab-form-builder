document.addEventListener('DOMContentLoaded', function() {
    const components = [];

    const componentMap = {
        input: createInputField,
        paragraph: createParagraph,
        textfield: createSubmitButton,
    };

    const draggables = document.querySelectorAll('.draggable');
    const dropzone = document.getElementById('dropzone');
    const formStructureInput = document.getElementById('form-structure');

    function dragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.id);
    }

    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    }

    function drop(e) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text/plain');
        addElementToDropzone(id);
    }

    function addElementToDropzone(id) {
        const component = componentMap[id];
        if (component) {
            const element = component();
            dropzone.appendChild(element);
        }
    }

    function createInputField() {
        const container = document.createElement('div');
        container.className = 'field-container';

        const label = document.createElement('label');
        label.textContent = 'Input Field:';
        container.appendChild(label);

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Enter text';
        container.appendChild(input);

        return container;
    }

    function createParagraph() {
        const p = document.createElement('p');
        p.textContent = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat quisquam aliquam esse dignissimos vel facilis voluptatem officia nisi ut! Excepturi, molestias? Laboriosam et perspiciatis quos facilis aliquam iure libero in?';
        return p;
    }

    function createSubmitButton() {
        const button = document.createElement('button');
        button.textContent = 'Submit';
        return button;
    }

    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', dragStart);
    });

    if (dropzone) {
        dropzone.addEventListener('dragover', dragOver);
        dropzone.addEventListener('drop', drop);
    }

    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();
        formStructureInput.value = dropzone.innerHTML;

        const formData = {
            form_name: document.getElementById('form-name').value,
            form_structure: dropzone.innerHTML,
        };

        fetch('/wp-json/nab-form-builder/v1/save-form', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nabFormBuilder.nonce
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Form saved:', data);
            window.location.href = '/wp-admin/admin.php?page=nab-saved-forms';
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });
});
