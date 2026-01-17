const addErrorToField = (field, msg = null) => {
    field.parentElement.classList.add("error");

    if (msg !== null) {
        let span = document.createElement("span");
        span.classList.add("error");

        span.innerText = msg;

        field.parentElement.after(span);
    }
}

const updateUserData = async (cardId) => {
    if (!confirm('Biztosan módosítja a felhasználó adatait?'))
        return;

    let card = document.getElementById(cardId);
    let form = card.querySelector('form');
    let formData = new FormData(form);
    let url = new URLSearchParams();
    let inputs = form.querySelectorAll("input,button");

    for (const kv of formData.entries())
        url.append(kv[0], kv[1]);

    const clearErrors = () => {
        card.querySelectorAll(".error").forEach((el) => {
            if (el.classList.contains("input-group"))
                el.classList.remove("error");

            else
                el.remove();
        });
    }

    toggleInputs(inputs);

    clearErrors();
    removeBanner();

    let resp = await fetch(form.action, {
        method: form.getAttribute("method"),
        body: url
    });

    setTimeout(async () => {
        toggleInputs(inputs);

        if (resp.status === 200) {
            return;

        } else if (resp.status === 422) {
            let body = JSON.parse(await resp.text());
            
            if (body["usernameError"] !== undefined)
                addErrorToField(document.getElementById("username"), body["usernameError"]);

            if (body["emailError"] !== undefined)
                addErrorToField(document.getElementById("email"), body["emailError"]);

            if (body["newPasswordError"] !== undefined)  {
                addErrorToField(document.getElementById("newPass"));
                addErrorToField(document.getElementById("confirmPass"), body["newPasswordError"]);
            }

        } else
            addErrorBanner("Az adatok frissítése nem sikerült");

    }, UI_LOADING_BUTTON_WAIT_TIME_MS);
}

const deleteUser = async (deleteForm) => {
    if (!confirm('Biztosan törli a felhasználót?'))
        return;

    let url = new URLSearchParams();
    let inputs = deleteForm.parentElement.querySelectorAll("input,button");
    let formData = new FormData(deleteForm);
    let deleteButton = deleteForm.querySelector(".button");

    toggleInputs(inputs, deleteButton);
    removeBanner();

    for (const kv of formData.entries())
        url.append(kv[0], kv[1]);

    let resp = await fetch(`${deleteForm.action}?${url}`, {
        method: deleteForm.getAttribute("method"),
    });

    setTimeout(() => {
        toggleInputs(inputs, deleteButton);

        if (resp.status !== 200) {
            addErrorBanner("A felhasználó törlése nem sikerült");
            return;
        }

        deleteForm.parentElement.animate([
            { transform: "scale(1)" },
            { transform: "scale(1.1)" },
            { transform: "scale(0)" }
        ], { 
            duration: 250,
            easing: "cubic-bezier(0.4,0,0.2,1)" 

        }).finished.finally(() => {
            let main = deleteForm.parentElement.parentElement;

            deleteForm.parentElement.previousElementSibling.remove();
            deleteForm.parentElement.remove();

            let cont = document.createElement("div");
            let span = document.createElement("span");
            let h1 = document.createElement("h1");
            let p = document.createElement("p");

            cont.classList.add("empty", "card-container");
            h1.innerText = "Keress felhasználókat";
            p.innerText = "Ha felhasználókra keresel rá, azok itt fognak megjelenni";

            span.appendChild(h1);
            span.append(p);

            cont.appendChild(span);
            main.appendChild(cont);
        });

    }, UI_LOADING_BUTTON_WAIT_TIME_MS);
}