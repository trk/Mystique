document.addEventListener("DOMContentLoaded", function (event) {
    let inputFields = document.querySelectorAll('[data-mystique-export="1"]');
    console.log(inputFields);
    inputFields.forEach(inputField => {
        console.log(inputField);
        inputField.addEventListener('click', function () {
            inputField.select();
        });
    });
});