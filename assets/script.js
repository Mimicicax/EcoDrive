const openModal = (mod) => mod.showModal();

const closeModal = (mod) => {
    // Nem használhatjuk a HTMLFormElement.reset()-et, mert az a default értékekre állítja vissza a formot.
    // Ha hiba történt, a default értékek a beküldött bemenetek és ezeket ki akarjuk szedni. Továbbá az error
    // classokat is el szeretnénk tüntetni ilyenkor.

    mod.querySelectorAll("form input").forEach((input) => {
        if (input.type != "submit")
            input.value = "";
    });

    mod.querySelectorAll(".error").forEach((el) => {
        if (el.classList.contains("input-group"))
            el.classList.remove("error");

        else
            el.remove();
    });

    mod.close();
}

const toggleInputs = (inputList, loadingButton = null) => {
    inputList.forEach((input) => {
        input.disabled = !input.disabled;

        if ((input === loadingButton && loadingButton !== null) || (loadingButton === null && input.classList.contains("button") && input.classList.contains("primary"))) {

            if (!input.classList.contains("loading")) {
                input.classList.add("loading");

                let spinner = document.createElement("i");

                spinner.setAttribute("class", "fa-solid fa-rotate fa-spin");
                spinner.innerHTML = "&nbsp;";

                input.appendChild(spinner);

            } else {
                input.classList.remove("loading");
                input.removeChild(input.lastChild);
            }
        }
    });
};

const updateVehicle = async (cardId) => {
    let card = document.getElementById(cardId);
    let form = card.querySelector("form");
    let formData = new FormData(form);
    let inputList = card.querySelectorAll("input,button");

    toggleInputs(inputList);

    const setError = (fieldName, errorText) => {
        let group = document.getElementById(cardId + "-" + fieldName).parentNode;
        let msg = document.createElement("span");

        msg.textContent = errorText;
        group.classList.add("error");
        msg.classList.add("error");

        if (group.parentNode.classList.contains("dual-input-group")) {
            msg.style = "margin:0.5em 0 1em 0";
            group.parentNode.after(msg);

        } else
            group.after(msg);
    }

    const clearErrors = () => {
        Array.prototype.forEach.call(card.querySelectorAll(".error"), (el) => {
            if (el.classList.contains("input-group"))
                el.classList.remove("error");

            else
                el.remove();
        });
    }

    clearErrors();

    let resp = await fetch(form.action, {
        method: "PUT",
        body: new URLSearchParams(formData)
    });

    if (resp.status == 200) {
        card.getElementsByClassName("car-license-plate")[0].textContent = formData.get("licensePlate");
        card.getElementsByClassName("car-brand")[0].textContent = formData.get("brand");
        card.getElementsByClassName("car-model")[0].textContent = formData.get("model");
        card.getElementsByClassName("car-year")[0].textContent = formData.get("year");

    } else if (resp.status == 400) {
        let errors = new URLSearchParams(await resp.text());
    
        errors.forEach((value, key) => {
            let fieldName = key.slice(0, key.length - "Error".length);
            setError(fieldName, value);
        });
    }

    setTimeout(() => toggleInputs(inputList), 200);
};

const deleteVehicle = async (cardId) => {
    if (!confirm('Biztosan törli a járművet?'))
        return;

    let card = document.getElementById(cardId);
    let inputList = card.querySelectorAll("input,button");
    let form = card.querySelector('form');
    let route = form.action;
    let id = form.querySelector('[type=hidden]').value;
    let deleteButton =card.querySelector(".danger");

    toggleInputs(inputList, deleteButton);

    let resp = await fetch(`${route}?vehicleId=${id}`, {
        method: "DELETE",
    });

    setTimeout(() => {
        toggleInputs(inputList, deleteButton);

        if (resp.status !== 200)
            return;

        card.animate([
            { transform: "scale(1)" },
            { transform: "scale(1.1)" },
            { transform: "scale(0)" }
        ], { 
            duration: 250,
            easing: "cubic-bezier(0.4,0,0.2,1)" 

        }).finished.finally(() => {
            card.remove();

            let cont = document.getElementById("vehicle-container");

            if (cont.children.length == 0) {
                cont.classList.add("empty",  "card-container");
                
                let span = document.createElement("span");
                let h1 = document.createElement("h1");
                let p = document.createElement("p");

                span.style.gridColumn = "1/-1";
                h1.append("Még nem mentettél el egy járművet sem");
                p.append("Amint hozzáadsz járműveket, azok itt fognak megjelenni");

                span.append(h1, p);
                cont.append(span);
            }
        });

    }, 200);
}

const saveProfileData = async (cardId) => {
    let cont = document.getElementById(cardId);
    let form = cont.querySelector('form');
    let inputList = form.querySelectorAll('input,button');
    let data = new FormData(form);
    let url = new URLSearchParams();

    for (const kv of data.entries())
        url.append(kv[0], kv[1]);

    toggleInputs(inputList);

    inputList.forEach((input) => {
        input.parentNode.classList.remove("error");
        
        if (input.parentNode.parentNode.classList.contains("dual-input-group"))
            input.parentNode.parentNode.querySelectorAll("span.error").forEach((span) => span.remove());
        
        else
            input.parentNode.querySelectorAll("span.error").forEach((span) => span.remove());
    });

    inputList[0].closest('.card').querySelectorAll('span.error').forEach((el) => {
        el.remove();
    })

    let resp = await fetch(form.action, {
        method: "PATCH",
        body: url
    });

    if (resp.status === 200) {

    } else {
        let errors = new URLSearchParams(await resp.text());

        errors.forEach((value, key) => {
            let fieldId = key.substring(0, key.length - "Error".length);
            let input = document.getElementById(fieldId);

            input.parentNode.classList.add("error");

            let msg = document.createElement("span");
            msg.classList.add("error");
            msg.textContent = value;

            if (input.parentNode.parentNode.classList.contains("dual-input-group"))
                input.parentNode.parentNode.after(msg);
            
            else
                input.parentNode.after(msg);
        });
    }

    setTimeout(() => toggleInputs(inputList), 200);
}

const deleteRoute = async (cardId) => {
    if (!confirm("Biztosan törli a bejegyzést?"))
        return;

    let card = document.getElementById(cardId);
    let form = card.querySelector('form');
    let inputs = form.querySelectorAll("button");
    let url = new URLSearchParams();
    
    for (const kv of (new FormData(form)).entries())
        url.append(kv[0], kv[1]);

    toggleInputs(inputs, inputs[0]);

    let resp = await fetch(`${form.action}?${url.toString()}`, {
        method: 'DELETE',
    });

    setTimeout(() => {
        toggleInputs(inputs, inputs[0]);

        if (resp.status !== 200)
            return;

        let prev = card.previousElementSibling;
        let next = card.nextElementSibling;
        let cont = document.getElementById("journal-container");
    
        card.animate([
            { transform: "scale(1)" },
            { transform: "scale(1.1)" },
            { transform: "scale(0)" }
        ], { 
            duration: 250,
            easing: "cubic-bezier(0.4,0,0.2,1)" 
        
        }).finished.finally(() => {
            card.remove();
        
            if (prev.tagName == "H2" && (next === null || next.tagName == "H2")) {
                prev.remove();
                card.remove();
            
                if (cont.childElementCount == 0) {
                    cont.previousElementSibling.remove();
                
                    cont.classList.add("empty",  "card-container");
                    
                    let span = document.createElement("span");
                    let h1 = document.createElement("h1");
                    let p = document.createElement("p");
                    let p2 = document.createElement("p");
                
                    span.style.gridColumn = "1/-1";
                    h1.append("A napló üres");
                    p.append("Adj hozzá útvonalakat és azok itt fognak megjelenni");
                    p2.append("Az is előfordulhat, hogy nincs a szűrési feltételeknek megfelelő elmentett útvonalad");
                
                    span.append(h1, p, p2);
                    cont.append(span);
                }
            }
        });

    }, 200);
}