@php
$segment_action = request()->segment(3);
$segment_id = request()->segment(4);
$items = [];
$canEdit = true; // Default for creation
$itemSelectDisabled = false; // Item selects should only be disabled based on edit status

// Debug information
$debugInfo = [
    'segment_action' => $segment_action,
    'segment_id' => $segment_id,
    'disabled' => $disabled ?? false
];

if ($segment_action == 'edit') {
    $items = \App\TrxPurchaseRequestsItems::where('trx_purchase_requests_id',$segment_id)->with('item')->get();
    // Check purchase request status for edit mode
    $purchaseRequest = \App\TrxPurchaseRequests::find($segment_id);
    $status = $purchaseRequest ? strtolower($purchaseRequest->doc_status) : 'unknown';
    $canEdit = $purchaseRequest && in_array($status, ['draft', 'rejected']);
    $itemSelectDisabled = !$canEdit; // Item selects disabled only when status is not draft/rejected

    // Add debug info
    $debugInfo['status'] = $status;
    $debugInfo['canEdit'] = $canEdit;
    $debugInfo['itemSelectDisabled'] = $itemSelectDisabled;
}
@endphp

<!-- Debug Information (remove this in production) -->
{{-- <div class="alert alert-info" style="font-size: 12px;">
    <strong>Debug Info:</strong>
    Action: {{ $debugInfo['segment_action'] ?? 'create' }} |
    Status: {{ $debugInfo['status'] ?? 'new' }} |
    Can Edit: {{ $debugInfo['canEdit'] ? 'true' : 'false' }} |
    Item Select Disabled: {{ $debugInfo['itemSelectDisabled'] ? 'true' : 'false' }} |
    Form Disabled: {{ $debugInfo['disabled'] ? 'true' : 'false' }}
</div> --}}

@if(!$disabled && $canEdit)
<!-- Toggle Button for Excel Import -->
<div class="row mb-3" id="excel-toggle-container" style="display:none;">
    <div class="col-sm-12">
        <button type="button" id="toggle-excel-import" class="btn btn-info btn-sm">
            <i class="fa fa-upload"></i> Tampilkan Impor Excel
        </button>
    </div>
</div>

<!-- Excel Import Instructions -->
<div class="alert alert-info" id="excel-instructions" style="display:none;">
    <strong><i class="fa fa-info-circle"></i> Instruksi Impor Excel:</strong>
    <br>
    <ul class="mb-0 ml-0 pl-0">
        <li><strong>Format:</strong> Gunakan format Excel (.xlsx atau .xls) dengan kolom: SKU, Nama Item, Kuantitas</li>
        <li><strong>Unduh Item:</strong> Klik "Unduh Item" untuk mendapatkan semua item yang tersedia untuk cabang yang dipilih</li>
        <li><strong>Validasi:</strong> Hanya item dengan kuantitas > 0 yang akan diimpor</li>
        <li><strong>Filtering:</strong> Item yang tidak ditemukan dalam opsi yang tersedia akan otomatis dilewati</li>
        <li><strong>Penggantian:</strong> Impor Excel akan mengganti semua item saat ini di tabel</li>
        <li><strong>Contoh:</strong> SKU: ITEM001, Nama Item: Produk Sampel, Kuantitas: 10</li>
    </ul>
</div>

<!-- Excel Import Section -->
<div class="box box-info" id="excel-import-section" style="display:none;">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <label>Unggah File Excel</label>
                <input type="file" id="excel-file" class="form-control" accept=".xlsx,.xls" />
            </div>
            <div class="col-sm-3">
                <label>&nbsp;</label><br>
                <button type="button" id="import-excel" class="btn btn-info">Impor Excel</button>
            </div>
            <div class="col-sm-3">
                <label>&nbsp;</label><br>
                <a href="#" id="download-template" class="btn btn-default">Unduh Item</a>
            </div>
        </div>
    </div>
</div>

<!-- Error Panel for Skipped Items -->
<div class="alert alert-warning" id="excel-errors" style="display:none;">
    <strong><i class="fa fa-exclamation-triangle"></i> Peringatan Impor:</strong>
    <br>
    <div id="excel-error-list"></div>
</div>

<!-- Success Panel for Import Results -->
<div class="alert alert-success" id="excel-success" style="display:none;">
    <strong><i class="fa fa-check-circle"></i> Hasil Impor:</strong>
    <br>
    <div id="excel-success-message"></div>
</div>
@endif

<!-- Scrollable Items Table Container -->
<div style="border: 1px solid #ddd; border-radius: 4px;">
    <table class="table table-bordered">
        <thead style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 10;">
            <tr>
                <th width="10%">No.</th>
                <th width="60%">Item</th>
                <th width="15%">Qty</th>
                @if(!$disabled && $canEdit) <th width="15%">Aksi</th> @endif
            </tr>
        </thead>
        <tbody id="item-table">
            @forelse($items as $index => $item)
                @include('merchandiser.purchase_request.partials.item_row', [
                    'index' => $index + 1,
                    'item' => $item,
                    'itemmaster' => $itemmaster,
                    'disabled' => $disabled,
                    'canEdit' => $canEdit,
                    'itemSelectDisabled' => $itemSelectDisabled,
                    'isFirst' => $index === 0
                ])
            @empty
                @include('merchandiser.purchase_request.partials.item_row', [
                    'index' => 1,
                    'item' => null,
                    'itemmaster' => $itemmaster,
                    'disabled' => $disabled,
                    'canEdit' => $canEdit,
                    'itemSelectDisabled' => $itemSelectDisabled,
                    'isFirst' => true
                ])
            @endforelse
        </tbody>
        @if(!$disabled && $canEdit)
        <tfoot style="background-color: #f9f9f9; z-index: 10;">
            <tr>
                <td colspan="3"></td>
                <td>
                    <button type="button" id="add-more" class="btn btn-xs btn-primary">
                        <i class="fa fa-plus"></i> Tambah Lagi
                    </button>
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
class ItemTableManager {
    constructor() {
        this.itemCounter = {{ count($items) ?: 1 }};
        this.itemMasterData = [];
        this.storeData = [];
        this.currentBranchId = null;
        this.isDisabled = {{ ($disabled ?? false) ? 'true' : 'false' }};
        this.canEdit = {{ $canEdit ? 'true' : 'false' }};
        this.itemSelectDisabled = {{ $itemSelectDisabled ? 'true' : 'false' }};

        // Debug log
        console.log('ItemTableManager Debug:', {
            isDisabled: this.isDisabled,
            canEdit: this.canEdit,
            itemSelectDisabled: this.itemSelectDisabled
        });

        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeBranchData();
    }

    bindEvents() {
        $(document).on('click', '#add-more', () => this.addNewRow());
        $(document).on('click', '.remove-item', (e) => this.removeRow(e));
        $('#Branch').on('change', (e) => this.handleBranchChange(e));
        $('#import-excel').on('click', () => this.importExcel());
        $('#download-template').on('click', (e) => this.downloadTemplate(e));
        $('#toggle-excel-import').on('click', () => this.toggleExcelImport());

        // Form validation
        $('#form').on('submit', (e) => this.validateForm(e));

        let lastSelectedValues = {};

        $(document).on('focus', '.select-item-master-data', function() {
            // Store the previous value on focus
            lastSelectedValues[this] = $(this).val();
        });

        $(document).on('change', '.select-item-master-data', function() {
            const selectedValue = $(this).val();
            let isDuplicate = false;

            $('.select-item-master-data').not(this).each(function() {
                if ($(this).val() === selectedValue && selectedValue !== "") {
                    isDuplicate = true;
                    return false; // break loop
                }
            });

            if (isDuplicate) {
                // Show SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Item Duplikat',
                    text: "Anda tidak bisa memilih opsi yang sama"
                });

                // Reset to empty value
                $(this).val('').trigger('change.select2');
            } else {
                lastSelectedValues[this] = selectedValue;
            }
        });
    }

    generateItemOptions(selectedId = '') {
        let options = '<option value="">-Pilih Item-</option>';
        this.itemMasterData.forEach(item => {
            const selected = item.id == selectedId ? 'selected' : '';
            options += `<option value="${item.id}" ${selected}>${item.name} [${item.sku}] [${item.unit_of_measurement}]</option>`;
        });
        return options;
    }

    addNewRow(itemId = '', qty = '') {
        this.itemCounter++;
        const options = this.generateItemOptions(itemId);
        const isSelectDisabled = this.itemSelectDisabled;

        const html = `
            <tr class="item-row" data-row="${this.itemCounter}">
                <td>${this.itemCounter}.</td>
                <td>
                    <select name="items[name][]" class="form-control select-item-master-data" ${isSelectDisabled ? 'disabled' : ''}>
                        ${options}
                    </select>
                    ${isSelectDisabled && itemId ? `<input type="hidden" name="items[name][]" value="${itemId}">` : ''}
                </td>
                <td>
                    <input type="number" name="items[qty][]" class="form-control" value="${qty}" min="1" ${this.isDisabled ? 'disabled' : ''}>
                </td>
                ${this.canEdit && !this.isDisabled ? `
                <td>
                    <button type="button" class="btn btn-xs btn-danger remove-item">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </td>` : ''}
            </tr>
        `;

        $("#item-table").append(html);
        this.initializeSelect2();
    }

    removeRow(event) {
        $(event.target).closest('tr').remove();
        this.renumberRows();
        this.updateItemCounter();
    }

    renumberRows() {
        $('#item-table tr').each(function(index) {
            const rowNumber = index + 1;
            $(this).find('td:first').text(rowNumber + '.');
            $(this).attr('data-row', rowNumber);
        });
    }

    updateItemCounter() {
        this.itemCounter = $('#item-table tr').length;
    }

    initializeSelect2() {
        const isSelectDisabled = this.itemSelectDisabled;
        $('.select-item-master-data').select2({
            placeholder: "Pilih item",
            allowClear: true,
            disabled: isSelectDisabled
        });
    }

    async loadItems(branchId) {
        try {
            const response = await $.get(`{{ urlsslcheck(CRUDBooster::mainpath()) }}/items?branch_id=${branchId}`);
            this.itemMasterData = response.data;
        } catch (error) {
            console.error('Gagal memuat item:', error);
        }
    }

    async loadStores(branchId) {
        try {
            const response = await $.get(`{{ urlsslcheck(CRUDBooster::mainpath()) }}/store?branch_id=${branchId}`);
            this.storeData = response.data;
        } catch (error) {
            console.error('Gagal memuat toko:', error);
        }
    }

    updateItemSelects() {
        const currentValues = $('.select-item-master-data').map(function() {
            return $(this).val();
        }).get();

        const options = this.generateItemOptions();
        const isSelectDisabled = this.itemSelectDisabled;

        $('.select-item-master-data').html(options).prop('disabled', isSelectDisabled);

        $('.select-item-master-data').each(function(index) {
            if (currentValues[index]) {
                $(this).val(currentValues[index]);

                // Add hidden input for disabled selects
                if (isSelectDisabled) {
                    const $hiddenInput = $(this).siblings('input[type="hidden"]');
                    if ($hiddenInput.length === 0) {
                        $(this).after(`<input type="hidden" name="items[name][]" value="${currentValues[index]}">`);
                    } else {
                        $hiddenInput.val(currentValues[index]);
                    }
                }
            }
        });

        this.initializeSelect2();
    }

    updateStoreSelect() {
        const currentValue = $("#U_VIT_ToStr").val();
        let storeOptions = '<option value="">- Pilih Toko -</option>';

        this.storeData.forEach(store => {
            storeOptions += `<option value="${store.key}">${store.label}</option>`;
        });

        $('#U_VIT_ToStr').html(storeOptions).val(currentValue);
        $('#U_VIT_ToStr').select2();
    }

    async handleBranchChange(event) {
        const branchId = $(event.target).val();
        this.currentBranchId = branchId;

        @if(!$disabled && $canEdit)
        // Show/hide toggle button based on branch selection
        if (branchId) {
            $('#excel-toggle-container').show();
        } else {
            $('#excel-toggle-container').hide();
            $('#excel-import-section').hide();
            $('#excel-instructions').hide();
            $('#excel-file').val('');
            // Reset toggle button text
            $('#toggle-excel-import').html('<i class="fa fa-upload"></i> Tampilkan Impor Excel');
        }
        @endif

        if (branchId) {
            await Promise.all([
                this.loadItems(branchId),
                this.loadStores(branchId)
            ]);

            setTimeout(() => {
                this.updateItemSelects();
                this.updateStoreSelect();
            }, 100);
        }
    }

    async initializeBranchData() {
        const branchId = $("#Branch").val();
        if (branchId) {
            this.currentBranchId = branchId;

            @if(!$disabled && $canEdit)
            // Show toggle button when branch is already selected
            $('#excel-toggle-container').show();
            @endif

            await Promise.all([
                this.loadItems(branchId),
                this.loadStores(branchId)
            ]);

            setTimeout(() => {
                this.updateItemSelects();
                this.updateStoreSelect();
            }, 1000);
        }

        this.initializeSelect2();
    }

    downloadTemplate(event) {
        event.preventDefault();

        if (this.itemMasterData.length === 0) {
            this.showErrorPanel('Silakan pilih cabang terlebih dahulu untuk memuat item', []);
            return;
        }

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const wsData = [['SKU', 'Nama Item', 'Kuantitas']];

        this.itemMasterData.forEach(item => {
            wsData.push([item.sku, item.name, 0]);
        });

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        XLSX.utils.book_append_sheet(wb, ws, 'Items');

        // Download file
        XLSX.writeFile(wb, 'items_template.xlsx');
    }

    importExcel() {
        const fileInput = document.getElementById('excel-file');
        const file = fileInput.files[0];

        if (!file) {
            this.showErrorPanel('Silakan pilih file Excel terlebih dahulu', []);
            return;
        }

        if (!file.name.toLowerCase().match(/\.(xlsx|xls)$/)) {
            this.showErrorPanel('Silakan pilih file Excel yang valid (.xlsx atau .xls)', []);
            return;
        }

        if (this.itemMasterData.length === 0) {
            this.showErrorPanel('Silakan pilih cabang terlebih dahulu untuk memuat item yang tersedia', []);
            return;
        }

        // Hide previous messages
        $('#excel-errors').hide();
        $('#excel-success').hide();

        // Show loading indicator
        $('#import-excel').prop('disabled', true).text('Memproses...');

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const parseResult = this.parseExcel(e.target.result);
                const { validData, skippedItems } = parseResult;

                if (validData.length > 0) {
                    this.replaceTableWithExcelData(validData);
                    $('#excel-file').val('');

                    // Show success message
                    let successMessage = `${validData.length} item berhasil diimpor!`;
                    this.showSuccessPanel(successMessage);

                    // Show warnings if any items were skipped
                    if (skippedItems.length > 0) {
                        this.showErrorPanel(`${skippedItems.length} item dilewati:`, skippedItems);
                    }
                } else {
                    if (skippedItems.length > 0) {
                        this.showErrorPanel('Tidak ada item yang valid dalam Excel. Semua item dilewati:', skippedItems);
                    } else {
                        this.showErrorPanel('Tidak ada item yang valid dalam Excel. Silakan periksa format dan pastikan kuantitas lebih dari 0.', []);
                    }
                }
            } catch (error) {
                console.error('Error impor Excel:', error);
                this.showErrorPanel('Terjadi kesalahan saat mengimpor file Excel. Silakan periksa format file.', []);
            } finally {
                // Reset button
                $('#import-excel').prop('disabled', false).text('Impor Excel');
            }
        };

        reader.onerror = () => {
            this.showErrorPanel('Terjadi kesalahan saat membaca file Excel', []);
            $('#import-excel').prop('disabled', false).text('Impor Excel');
        };

        reader.readAsArrayBuffer(file);
    }

    parseExcel(arrayBuffer) {
        const workbook = XLSX.read(arrayBuffer, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

        const validData = [];
        const skippedItems = [];

        if (jsonData.length < 2) {
            throw new Error('File Excel harus berisi setidaknya satu baris header dan satu baris data');
        }

        // Skip header row and process data rows
        for (let i = 1; i < jsonData.length; i++) {
            const row = jsonData[i];
            const rowNumber = i + 1; // Excel row number (1-based, accounting for header)

            // Skip completely empty rows
            if (!row || row.every(cell => !cell && cell !== 0)) {
                continue;
            }

            if (row.length < 2) {
                skippedItems.push({
                    rowNumber: rowNumber,
                    reason: 'Kolom tidak cukup (minimal 2 diperlukan: SKU dan Kuantitas)',
                    data: row
                });
                continue;
            }

            const sku = String(row[0] || '').trim();
            // Handle both old format (SKU, Quantity) and new format (SKU, Item Name, Quantity)
            const quantity = row.length >= 3 ? parseInt(row[2]) : parseInt(row[1]);

            // Validate SKU
            if (!sku) {
                skippedItems.push({
                    rowNumber: rowNumber,
                    reason: 'SKU kosong atau tidak valid',
                    data: row
                });
                continue;
            }

            // Validate quantity
            if (isNaN(quantity)) {
                skippedItems.push({
                    rowNumber: rowNumber,
                    reason: 'Kuantitas tidak valid (bukan angka)',
                    data: row
                });
                continue;
            }

            if (quantity <= 0) {
                skippedItems.push({
                    rowNumber: rowNumber,
                    reason: 'Kuantitas harus lebih dari 0',
                    data: row
                });
                continue;
            }

            // Find item in master data
            const foundItem = this.itemMasterData.find(item =>
                item.sku && item.sku.toLowerCase() === sku.toLowerCase()
            );

            if (!foundItem) {
                skippedItems.push({
                    rowNumber: rowNumber,
                    reason: `SKU "${sku}" tidak ditemukan dalam item yang tersedia untuk cabang ini`,
                    data: row
                });
                continue;
            }

            // Valid item
            validData.push({
                item: foundItem,
                qty: quantity
            });
        }

        return { validData, skippedItems };
    }

    showSuccessPanel(message) {
        $('#excel-success-message').text(message);
        $('#excel-success').show();

        // Auto hide success message after 5 seconds
        setTimeout(() => {
            $('#excel-success').fadeOut();
        }, 5000);
    }

    showErrorPanel(message, skippedItems) {
        let errorHtml = `<p>${message}</p>`;

        if (skippedItems.length > 0) {
            errorHtml += '<ul class="mb-0">';
            skippedItems.forEach(skipped => {
                const dataPreview = skipped.data.slice(0, 3).join(' | ');
                errorHtml += `<li><strong>Baris ${skipped.rowNumber}:</strong> ${skipped.reason} (Data: ${dataPreview})</li>`;
            });
            errorHtml += '</ul>';
        }

        $('#excel-error-list').html(errorHtml);
        $('#excel-errors').show();
    }

    displaySkippedItems(skippedItems) {
        this.showErrorPanel(`${skippedItems.length} item dilewati:`, skippedItems);
    }

    replaceTableWithExcelData(excelData) {
        // Clear existing rows
        $('#item-table').empty();
        this.itemCounter = 0;

        if (excelData.length === 0) {
            // Add empty row if no data
            this.addNewRow();
            return;
        }

        const isSelectDisabled = this.itemSelectDisabled;

        excelData.forEach((data, index) => {
            this.itemCounter++;
            const options = this.generateItemOptions(data.item.id);

            const html = `
                <tr class="item-row" data-row="${this.itemCounter}">
                    <td>${this.itemCounter}.</td>
                    <td>
                        <select name="items[name][]" class="form-control select-item-master-data" ${isSelectDisabled ? 'disabled' : ''}>
                            ${options}
                        </select>
                        ${isSelectDisabled ? `<input type="hidden" name="items[name][]" value="${data.item.id}">` : ''}
                    </td>
                    <td>
                        <input type="number" name="items[qty][]" class="form-control" value="${data.qty}" ${this.isDisabled ? 'disabled' : ''}>
                    </td>
                    ${this.canEdit && !this.isDisabled ? `
                    <td>
                        <button type="button" class="btn btn-xs btn-danger remove-item">
                            <i class="fa fa-trash"></i> Hapus
                        </button>
                    </td>` : ''}
                </tr>
            `;

            $("#item-table").append(html);
        });

        // Initialize Select2 after adding all rows
        setTimeout(() => {
            this.initializeSelect2();
        }, 100);
    }

    toggleExcelImport() {
        const excelSection = $('#excel-import-section');
        const excelInstructions = $('#excel-instructions');
        const button = $('#toggle-excel-import');

        if (excelSection.is(':visible')) {
            excelSection.slideUp();
            excelInstructions.slideUp();
            // Hide messages when closing
            $('#excel-errors').hide();
            $('#excel-success').hide();
            button.html('<i class="fa fa-upload"></i> Tampilkan Impor Excel');
        } else {
            excelSection.slideDown();
            excelInstructions.slideDown();
            button.html('<i class="fa fa-times"></i> Sembunyikan Impor Excel');
        }
    }

    validateForm(event) {
        const docDate = $('#DocDate').val();
        const currentDate = new Date().toISOString().split('T')[0];
        const $submitButtons = $('input[type="submit"], button[type="submit"]');

        // Disable submit buttons immediately
        $submitButtons.prop('disabled', true);

        // Validation: check for empty selects, qty, and duplicates
        let hasEmptySelect = false;
        let hasEmptyQty = false;
        let hasDuplicates = false;
        const itemValues = [];

        $('.select-item-master-data').each(function() {
            const value = $(this).val();
            if (!value) {
                hasEmptySelect = true;
                return false; // break
            }
            if (itemValues.includes(value)) {
                hasDuplicates = true;
                return false; // break
            }
            itemValues.push(value);
        });
        $('input[name="items[qty][]"]').each(function() {
            if (!$(this).val() || isNaN($(this).val()) || Number($(this).val()) <= 0) {
                hasEmptyQty = true;
                return false; // break
            }
        });
        if (hasEmptySelect) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error Validasi',
                text: 'Silakan pilih item untuk semua baris.'
            });
            $submitButtons.prop('disabled', false);
            return false;
        }
        if (hasEmptyQty) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error Validasi',
                text: 'Silakan masukkan kuantitas yang valid (lebih dari 0) untuk semua item.'
            });
            $submitButtons.prop('disabled', false);
            return false;
        }
        if (hasDuplicates) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Peringatan',
                text: 'Peringatan: Terdapat item yang duplikat. Silakan hapus item duplikat sebelum melanjutkan.'
            });
            $submitButtons.prop('disabled', false);
            return false;
        }

        if (!docDate) {
            event.preventDefault();
            alert("Silakan pilih Tanggal Dokumen (Doc. Date)");
            $submitButtons.prop('disabled', false);
            return false;
        } else if (docDate < currentDate) {
            event.preventDefault();
            alert("Tanggal mundur (back date) tidak diperbolehkan");
            $submitButtons.prop('disabled', false);
            return false;
        }else if(docDate > currentDate){
            event.preventDefault();
            alert("Tanggal di masa depan tidak diperbolehkan");
            $submitButtons.prop('disabled', false);
            return false;
        }

        // If all validations pass, form will submit with buttons disabled
        return true;
    }
}

// Check for duplicate items
function checkForDuplicateItems() {
    const itemSelects = $('.select-item-master-data');
    const itemValues = [];
    let hasDuplicates = false;

    itemSelects.each(function() {
        const value = $(this).val();
        if (value) {
            if (itemValues.includes(value)) {
                hasDuplicates = true;
                return false; // break the loop
            }
            itemValues.push(value);
        }
    });

    return !hasDuplicates;
}

// Initialize the manager when document is ready
$(document).ready(function() {
    @if($disabled)
    $('#parent-form-area > div.box-footer > div > div > input').remove();
    // Hide all submit buttons when disabled
    $('input[type="submit"], button[type="submit"]').hide();
    @endif

    new ItemTableManager();
});
</script>

<style>
/* Custom SweetAlert2 size and font overrides */
.swal2-popup, .sweet-alert {
    max-width: 90vw !important;
    font-size: 1.5rem !important;
    padding: 2.5em 2em !important;
}
.swal2-title, .sweet-alert h2 {
    font-size: 2.2rem !important;
    line-height: 1.3 !important;
}
.swal2-html-container, .sweet-alert p {
    font-size: 1.8rem !important;
    line-height: 1.5 !important;
}
.swal2-actions button, .sweet-alert button {
    font-size: 1.2rem !important;
    padding: 0.8em 2.2em !important;
}
</style>
