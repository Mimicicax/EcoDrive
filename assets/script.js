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

const deleteVehicle = async (cardId, plate) => {
    let card = document.getElementById(cardId);
    let inputList = card.querySelectorAll("input,button");

    inputList.forEach((input) => input.disabled = true);

    let resp = await fetch(`/vehicles?licensePlate=${encodeURIComponent(plate)}`, {
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
        inputList.forEach((input) => input.disabled = true);
}