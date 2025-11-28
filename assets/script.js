const openModal = (mod) => mod.showModal();

const closeModal = (mod) => {
    mod.querySelector("form").reset();
    mod.close();
}