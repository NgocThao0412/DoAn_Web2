let selectedProducts = [];
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const importId = urlParams.get('id');

    if (importId) {
        document.getElementById('import_id').value = importId;
        loadExistingImport(importId);
    }
});

async function loadExistingImport(id) {
    try {
        const response = await fetch(`Api_php/get-import-detail.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const importData = result.import;
            
            document.getElementById('supplier_name').value = importData.supplier_name || '';
            document.getElementById('supplier_phone').value = importData.supplier_phone || '';
            document.getElementById('supplier_address').value = importData.supplier_address || '';

            if (importData.status === 'completed') {
                document.getElementById('supplier_name').disabled = true;
                document.getElementById('supplier_phone').disabled = true;
                document.getElementById('supplier_address').disabled = true;
                document.getElementById('searchProductInput').disabled = true;
                
                const actionButtons = document.querySelector('.action-buttons');
                if(actionButtons) actionButtons.style.display = 'none';

                const title = document.querySelector('.text-big');
                if(title) title.innerText = `Chi tiết Phiếu Nhập #${id} (Đã Chốt)`;
            } else {
                const title = document.querySelector('.text-big');
                if(title) title.innerText = `Đang sửa Phiếu Nhập Nháp #${id}`;
            }

            if (result.details && result.details.length > 0) {
                selectedProducts = result.details.map(d => ({
                    product_id: d.product_id,
                    name: d.name,
                    quantity: d.quantity,
                    import_price: d.import_price
                }));
                renderImportTable(importData.status === 'completed');
            }
        } else {
            alert("Lỗi tải chi tiết phiếu: " + result.message);
        }
    } catch (error) {
        console.error("Lỗi khi fetch chi tiết:", error);
    }
}

function searchProductAPI() {
    const keyword = document.getElementById('searchProductInput').value.trim();
    const suggestBox = document.getElementById('productSuggestBox');

    if (keyword.length === 0) {
        suggestBox.style.display = 'none';
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`Api_php/search-products.php?q=${encodeURIComponent(keyword)}`);
            const products = await response.json();
            
            suggestBox.innerHTML = ''; 
            
            if (products.length > 0) {
                products.forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'suggest-item';
                    div.innerHTML = `<span><b>${p.name}</b></span> <span style="color:#888;">Tồn kho: ${p.current_stock}</span>`;
                    div.onclick = () => selectProduct(p.product_id, p.name);
                    suggestBox.appendChild(div);
                });
                suggestBox.style.display = 'block';
            } else {
                suggestBox.innerHTML = '<div style="padding: 12px; color: red;">Không tìm thấy sản phẩm nào!</div>';
                suggestBox.style.display = 'block';
            }
        } catch (error) {
            console.error("Lỗi tìm sản phẩm:", error);
        }
    }, 300);
}

function selectProduct(id, name) {
    const existing = selectedProducts.find(p => p.product_id === id);
    if (existing) {
        existing.quantity += 1; 
    } else {
        selectedProducts.push({
            product_id: id,
            name: name,
            quantity: 1,
            import_price: 0 
        });
    }

    document.getElementById('productSuggestBox').style.display = 'none';
    document.getElementById('searchProductInput').value = '';
    renderImportTable();
}

function updateDetail(id, field, value) {
    const product = selectedProducts.find(p => p.product_id === id);
    if (product) {
        // --- CHỖ NÀY: Xử lý làm sạch dấu chấm cho giá nhập ---
        let val;
        if (field === 'import_price') {
            val = value.toString().replace(/\./g, ''); // Xóa dấu chấm
            val = parseFloat(val) || 0;
        } else {
            val = Number(value);
        }
        
        if(val < 0) val = 0; 
        product[field] = val;
        renderImportTable(); 
    }
}

function removeProduct(id) {
    selectedProducts = selectedProducts.filter(p => p.product_id !== id);
    renderImportTable();
}

function renderImportTable(isCompleted = false) {
    const tbody = document.getElementById('importDetailBody');
    tbody.innerHTML = '';
    let totalAmount = 0;

    if(selectedProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; color: #888;">Chưa có sản phẩm nào. Hãy tìm kiếm ở trên!</td></tr>';
        document.getElementById('totalImportAmount').innerText = '0 VNĐ';
        return;
    }

    selectedProducts.forEach(p => {
        const rowTotal = p.quantity * p.import_price;
        totalAmount += rowTotal;

        const tr = document.createElement('tr');
        
        if (isCompleted) {
            tr.innerHTML = `
                <td style="text-align: left;">${p.name}</td>
                <td><input type="number" value="${p.quantity}" disabled style="background:#f1f1f1;"></td>
                <td><input type="text" value="${parseFloat(p.import_price).toLocaleString('vi-VN')}" disabled style="background:#f1f1f1;"></td>
                <td style="color: #001f3f; font-weight: bold;">${rowTotal.toLocaleString('vi-VN')} đ</td>
                <td><span style="color:#ccc;">Không thể xóa</span></td>
            `;
        } else {
            tr.innerHTML = `
                <td style="text-align: left;">${p.name}</td>
                <td><input type="number" min="1" value="${p.quantity}" onchange="updateDetail(${p.product_id}, 'quantity', this.value)"></td>
                <td><input type="text" value="${p.import_price}" onchange="updateDetail(${p.product_id}, 'import_price', this.value)"></td>
                <td style="color: #001f3f; font-weight: bold;">${rowTotal.toLocaleString('vi-VN')} đ</td>
                <td><button class="btn-remove" onclick="removeProduct(${p.product_id})">Xóa</button></td>
            `;
        }
        tbody.appendChild(tr);
    });

    document.getElementById('totalImportAmount').innerText = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
}

async function saveImport(status) {
    if (selectedProducts.length === 0) {
        alert("Vui lòng thêm ít nhất 1 sản phẩm vào phiếu nhập!");
        return;
    }

    const hasZeroPrice = selectedProducts.some(p => p.import_price <= 0);
    if (hasZeroPrice) {
        alert("Vui lòng nhập giá nhập lớn hơn 0 cho tất cả sản phẩm!");
        return;
    }

    if(status === 'completed') {
        const confirmCheck = confirm("Bạn có chắc chắn muốn chốt phiếu này không? Sau khi chốt, số lượng sẽ được cộng vào kho và không thể sửa lại!");
        if(!confirmCheck) return;
    }

    // --- CHỖ NÀY: Làm sạch mảng details một lần nữa trước khi gửi ---
    const cleanedDetails = selectedProducts.map(p => ({
        product_id: p.product_id,
        quantity: p.quantity,
        import_price: p.import_price.toString().replace(/\./g, '') // Chắc chắn gửi số nguyên sạch
    }));

    const requestData = {
        import_id: document.getElementById('import_id').value, 
        supplier_name: document.getElementById('supplier_name').value.trim(),
        supplier_phone: document.getElementById('supplier_phone').value.trim(),
        supplier_address: document.getElementById('supplier_address').value.trim(),
        status: status, 
        details: cleanedDetails 
    };

    try {
        const response = await fetch('Api_php/save-import.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        const result = await response.json();
        
        if (result.success) {
            alert(status === 'draft' ? "Lưu nháp thành công!" : "Đã chốt phiếu nhập thành công!");
            window.location.href = "import-list"; 
        } else {
            alert("Lỗi: " + result.message);
        }
    } catch (error) {
        console.error("Lỗi khi lưu phiếu:", error);
        alert("Có lỗi xảy ra khi kết nối với server!");
    }
}