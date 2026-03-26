
const citySelect = document.getElementById("editCity");
const wardSelect = document.getElementById("editWard");

const cityHidden = document.getElementById("editCityName");
const wardHidden = document.getElementById("editWardName");

// load tỉnh
fetch("includes/getProvinces.php")
    .then(res => res.json())
    .then(data => {
        citySelect.innerHTML = "<option value=''>Chọn tỉnh</option>";

        data.forEach(item => {
            const name = item.provinceName || item.name;
            const id = item.provinceID || item.id;

            citySelect.add(new Option(name, id));
        });
    });

// load phường
citySelect.addEventListener("change", function () {
    const provinceID = this.value;

    cityHidden.value = this.options[this.selectedIndex]?.text || "";

    wardSelect.innerHTML = "<option value=''>Chọn phường</option>";

    if (provinceID) {
        fetch(`includes/getWard.php?provinceID=${provinceID}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(item => {
                    const name = item.wardName || item.name;
                    const id = item.wardID || item.id;

                    wardSelect.add(new Option(name, id));
                });
            });
    }
});

wardSelect.addEventListener("change", function () {
    wardHidden.value = this.options[this.selectedIndex]?.text || "";
});
