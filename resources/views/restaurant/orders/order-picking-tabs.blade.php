<style>
    .nav-pills .nav-link {
        font-size: 0.85rem;
        /* Slightly reduced font size */
        padding: 0.4rem 0.75rem;
        /* Optional: tighter padding */
        white-space: nowrap;
        /* Prevents text from wrapping */
    }

    .nav-pills {
        flex-wrap: nowrap;
        overflow-x: auto;
        /* Allows horizontal scrolling as a fallback */
    }

    .nav-item {
        flex-shrink: 0;
    }
</style>
<ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
    <li class="nav-item toBePickedTab" role="presentation">
        <button class="nav-link active" id="pills-to_be_picked-tab" data-bs-toggle="pill" data-bs-target="#to_be_picked"
            type="button" role="tab" aria-controls="to_be_picked" aria-selected="true">To be picked
        </button>
    </li>
    <li class="nav-item pickedTab" role="presentation">
        <button class="nav-link" id="pills-picked-tab" data-bs-toggle="pill" data-bs-target="#picked" type="button"
            role="tab" aria-controls="picked" aria-selected="false">Picked
            ({{ $pickedProductsCount }})
        </button>
    </li>
    <li class="nav-item notThereTab" role="presentation">
        <button class="nav-link" id="pills-not_there-tab" data-bs-toggle="pill" data-bs-target="#not_there"
            type="button" role="tab" aria-controls="not_there" aria-selected="false">Out of Stock
            ({{ $outOfStockProductsCount }})
        </button>
    </li>
    <li class="nav-item editedTab" role="presentation">
        <button class="nav-link" id="pills-edited-tab" data-bs-toggle="pill" data-bs-target="#edited" type="button"
            role="tab" aria-controls="edited" aria-selected="false">Edited
            ({{ $editedProductsCount }})
        </button>
    </li>
    <li class="nav-item locationIncorrectTab" role="presentation">
        <button class="nav-link" id="pills-location_incorrect-tab" data-bs-toggle="pill"
            data-bs-target="#location_incorrect" type="button" role="tab" aria-controls="location_incorrect"
            aria-selected="false">Location Incorrect
            ({{ $incorrectLocationProductsCount }})
        </button>
    </li>
</ul>
