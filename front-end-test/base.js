function renderErrors(errors) {
    const errorsDiv = document.getElementById("errors");
    errorsDiv.innerHTML = ""; 

    if (errors.length > 0) {
        errors.forEach(err => {
            const div = document.createElement("div");
            div.innerHTML = "&raquo; " + err;
            errorsDiv.appendChild(div);
        });
        errorsDiv.style.display = "block";
    } else {
        errorsDiv.style.display = "none";
    }
}

function validateForm() {
    let errors = [];

    if (document.getElementById("full-name").value.trim() === "") {
        errors.push("Lauks 'Vārds, Uzvārds' ir jānorāda obligāti");
    }
    if (document.getElementById("telefonanr").value.trim() === "") {
        errors.push("Lauks 'Telefona numurs' ir jānorāda obligāti");
    }
    if (document.getElementById("zinojums").value.trim() === "") {
        errors.push("Lauks 'Ziņojums' ir jānorāda obligāti");
    }

    renderErrors(errors);
    return errors.length === 0;
}

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contact-form");

    form.addEventListener("submit", (e) => {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
});
