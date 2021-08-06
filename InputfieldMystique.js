document.addEventListener("DOMContentLoaded", function (event) {
    let inputFields = document.querySelectorAll('[data-mystique-export="1"]');
    inputFields.forEach(inputField => {
        console.log(inputField);
        inputField.addEventListener('click', function () {
            inputField.select();
        });
    });
});