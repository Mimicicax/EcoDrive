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

const toggleInputs = (inputList) => {
    inputList.forEach((input) => input.disabled = !input.disabled);
};

const updateVehicle = async (cardId, plate) => {
    let card = document.getElementById(cardId);
    let form = card.querySelector("form");
    let formData = new FormData(form);
    let inputList = card.querySelectorAll("input,button");

    toggleInputs(inputList);

    const setError = (fieldName, errorText) => {
        let group = document.getElementById(plate + "-" + fieldName).parentNode;
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

    let resp = await fetch(`/vehicles`, {
        method: "PUT",
        body: new URLSearchParams(formData)
    });

    if (resp.status == 200) {
        card.querySelector("[type=hidden]").value = formData.get("licensePlate");
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

    toggleInputs(inputList);
};

const deleteVehicle = async (cardId, plate, route) => {
    let card = document.getElementById(cardId);
    let inputList = card.querySelectorAll("input,button");

    toggleInputs(inputList);

    let resp = await fetch(`${route}?licensePlate=${encodeURIComponent(plate)}`, {
        method: "DELETE",
    });

    if (resp.status == 200) {
        card.animate([
            { transform: "scale(1)" },
            { transform: "scale(1.1)" },
            { transform: "scale(0)" }
        ], { 
            duration: 250,
            easing: "cubic-bezier(0.4,0,0.2,1)" 

        }).finished.finally(() => {
            card.remove();

            let cont = document.querySelector(".vehicle.card-container");

            if (cont.children.length == 0) {
                cont.classList.add("empty");
                
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

    } else
        toggleInputs(inputList);
}

const saveProfileData = async (fields, route) => {
    let data = new URLSearchParams();
    let inputList = [];

    fields.forEach((id) => {
        let input = document.getElementById(id);
        data.append(id, input.value);
        inputList.push(input);
    });

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

    let resp = await fetch(route, {
        method: "PATCH",
        body: data
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

    toggleInputs(inputList);
}