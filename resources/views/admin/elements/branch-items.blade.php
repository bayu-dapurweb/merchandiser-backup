@php

$items = \App\RefItemMasterData::whereRaw("(tags like '%NoN-Agrinesia%')")->orderBy('sku')->get();
$branch = \App\RefBranches::where('id', request()->segment(4))->first();
$branch_items = \App\ProductBranches::where('ref_branches_id', $branch->id)->get();

// dd(request()->segment(4));

@endphp

<div class="mb-3">
    <input type="text" class="form-control" id="searchInput" 
           placeholder="Search by Item Code or Name..." 
           onkeyup="searchItems()"
           onkeypress="return event.keyCode != 13;">
</div>

<div class="table-responsive" style="max-height: 400px; overflow-y: scroll">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"></th>
        <th>Item Code</th>
        <th>Item Name</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($items as $item)
      <tr>
        <td><input type="checkbox" name="items[]" value="{{ $item->id }}" {{ $branch_items->contains('ref_item_master_datas_id', $item->id) ? 'checked' : '' }}></td>
        <td>{{ $item->sku }} </td>
        <td>{{ $item->name }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="row mt-4 mb-3">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-12">
                        <h5 class="mb-1 text-primary" id="selectedItems">{{ $branch_items->count() }} of {{ count($items) }} items selected</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Export Items</h4>
            </div>
            <div class="card-body">
                <p class="card-text text-muted">Download all items as CSV file</p>
                <button type="button" class="btn btn-success" onclick="exportItems()">
                    Download CSV
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Bulk Select</h4>
            </div>
            <div class="card-body">
                <p class="card-text text-muted">Paste item codes (one per line) to select items</p>
                <div class="row">
                  <div class="col-md-9">
                    <textarea class="form-control" id="bulkImport" 
                        placeholder="Example:&#10;ITEM001&#10;ITEM002" 
                        rows="3"></textarea>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-success" style="width:100%;margin-left:-10px;" onclick="processImport()">
                      Process
                  </button>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  function updateCounts() {
    const selectedCheckboxes = document.querySelectorAll('input[name="items[]"]:checked');
    const totalItems = document.getElementById('totalItems').textContent;
    
    document.getElementById('selectedItems').textContent = `${selectedCheckboxes.length} / ${totalItems} items selected`;
  }

  function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('input[name="items[]"]');
    checkboxes.forEach(checkbox => {
      // Only toggle if parent row is visible
      if (checkbox.closest('tr').style.display !== 'none') {
        checkbox.checked = source.checked;
      }
    });
    updateCounts();
  }

  function searchItems() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const tbody = document.querySelector('tbody');
    const rows = tbody.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const itemCode = rows[i].getElementsByTagName('td')[1];
        const itemName = rows[i].getElementsByTagName('td')[2];
        
        if (itemCode && itemName) {
            const codeText = itemCode.textContent || itemCode.innerText;
            const nameText = itemName.textContent || itemName.innerText;
            
            if (codeText.toUpperCase().includes(filter) || nameText.toUpperCase().includes(filter)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
    updateCounts();
  }

  function processImport() {
    const textarea = document.getElementById('bulkImport');
    const codes = textarea.value.split(/[\n,]+/).map(code => code.trim()).filter(code => code);
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        const itemCode = row.getElementsByTagName('td')[1].textContent.trim();
        
        if (codes.includes(itemCode)) {
            checkbox.checked = true;
            textarea.value = ""
        }
    });
    
    // Update search input to show only matched items
    document.getElementById('searchInput').value = '';
    updateCounts();
  }

  function exportItems() {
    const rows = document.querySelectorAll('tbody tr');
    let csvContent = "Item Code,Item Name\n";
    
    rows.forEach(row => {
        // Skip hidden rows
        if (row.style.display === 'none') return;
        
        const cells = row.getElementsByTagName('td');
        // Check if cells exist
        if (cells.length >= 3) {
            const itemCode = cells[1].textContent?.trim() || '';
            const itemName = cells[2].textContent?.trim() || '';
            if (itemCode) {
                csvContent += `${itemCode},"${itemName}"\n`;
            }
        }
    });
    
    // Only create download if we have data
    if (csvContent.length > 0) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        
        link.setAttribute("href", url);
        link.setAttribute("download", "item_codes.csv");
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
  }

  // Add event listeners to update counts when checkboxes change
  document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="items[]"]');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', updateCounts);
    });
    updateCounts();
  });
</script>

