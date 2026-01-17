const addErrorToField = (field, msg = null) => {
    field.parentElement.classList.add("error");

    if (msg !== null) {
        let span = document.createElement("span");
        span.classList.add("error");

        span.innerText = msg;

        field.parentElement.after(span);
    }
}

const addErrorBanner = (msg) => {
    let banner = document.createElement("div");
    let content = document.createElement("p");  
    let close = document.createElement("i");

    banner.classList.add("error-banner");   

    content.innerHTML = `<i class=\"fa-solid fa-circle-exclamation banner-icon\"></i>${msg}`;

    close.classList.add("fa-solid", "fa-xmark", "banner-close-mark");
    close.addEventListener("click", () => banner.style.display = 'none');

    banner.appendChild(content);
    banner.appendChild(close);
    document.body.appendChild(banner);
}

const removeBanner = () => document.querySelector(".error-banner")?.remove();

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

    }, 200);
}