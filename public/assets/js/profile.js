
const citySelect = document.getElementById("editCity");
const wardSelect = document.getElementById("editWard");


const cityHidden = document.getElementById("editCityName");
const wardHidden = document.getElementById("editWardName");

fetch("includes/getProvinces.php")
    .then(res => res.json())
    .then(data => {
        citySelect.innerHTML = "<option value=''>Chọn tỉnh</option>";

        data.forEach(item => {
            const name = item.provinceName || item.name;
            const id = item.provinceID || item.id;

            const option = new Option(name, id);

            
            if (name === currentCity) {
                option.selected = true;

                
                loadWards(id);
            }

            citySelect.add(option);
        });

        
        cityHidden.value = currentCity;
    });

function loadWards(provinceID) {
    wardSelect.innerHTML = "<option value=''>Chọn phường</option>";

    fetch(`includes/getWard.php?provinceID=${provinceID}`)
        .then(res => res.json())
        .then(data => {
            data.forEach(item => {
                const name = item.wardName || item.name;
                const id = item.wardID || item.id;

                const option = new Option(name, id);

                
                if (name === currentWard) {
                    option.selected = true;
                }

                wardSelect.add(option);
            });

            wardHidden.value = currentWard;
        });
}

citySelect.addEventListener("change", function () {
    const provinceID = this.value;

    cityHidden.value = this.options[this.selectedIndex]?.text || "";

    loadWards(provinceID);
});

wardSelect.addEventListener("change", function () {
    wardHidden.value = this.options[this.selectedIndex]?.text || "";
});
