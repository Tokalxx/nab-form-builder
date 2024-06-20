/*
    Code within this file is suppose to handle the drag and drop function of the plugin.
*/ 


document.addEventListener('DOMContentLoaded', function() {
    const components = [];

    //component type mapping
    const componentMap = {
        input: createInputField,
        paragraph: createParagraph,
        textfield: createSubmitButton,
    };

    const draggables = document.querySelectorAll('.draggable');
    const dropzone = document.getElementById('dropzone');
    const formStructureInput = document.getElementById('form-structure');

    //function that starts the dragging of the element selected
    function dragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.id);
    }

    //function that lets the dragged element to be taken over the dropzone
    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    }

    //function that drops the element to the dropzone
    function drop(e) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text/plain');
        addElementToDropzone(id);
    }

    //function that adds the dropped element to the dropzone based on the element's id
    function addElementToDropzone(id) {
        const component = componentMap[id];
        if (component) {
            const element = component();
            dropzone.appendChild(element);
        }
    }

    //test input component
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

    //test paragraph component
    function createParagraph() {
        const p = document.createElement('p');
        p.textContent = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat quisquam aliquam esse dignissimos vel facilis voluptatem officia nisi ut! Excepturi, molestias? Laboriosam et perspiciatis quos facilis aliquam iure libero in?';
        return p;
    }

    //test submit button component
    function createSubmitButton() {
        const button = document.createElement('button');
        button.textContent = 'Submit';
        return button;
    }

    //Add event listeners to all draggable elements to handle the start of dragging.
    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', dragStart);
    });

    if (dropzone) {
        dropzone.addEventListener('dragover', dragOver);
        dropzone.addEventListener('drop', drop);
    }

    //handles the submission of the form to the database
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
