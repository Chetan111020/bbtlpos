<div class="row">
    <div class="col-md-12 p-3">
        @if ($orderProduct != null)
            <form id="picked_add_form"
                action="{{ action('Restaurant\KitchenApiController@pickProduct', [$orderProduct->id]) }}">
                <input id="pickedQty" type="hidden" name="updatedPickedQty" value="">
                <button type="submit" id="pickingItem" class="btn btn-lg pickedBtn form-control">Picked
                </button>
            </form>
        @else
            <a href="#" type="button" class="btn btn-lg pickedBtn form-control">Picked</a>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-12 p-3">
        @if ($orderProduct != null)
            <a href="{{ action('Restaurant\KitchenController@outOfStock', [$orderProduct->id]) }}" type="button"
                class="btn btn-lg editedBtn form-control">Out Of Stock
            </a>
        @else
            <a href="#" type="button" class="btn btn-lg editedBtn form-control">Out
                Of Stock
            </a>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-12 p-3">
        <button onclick="editPickingQty(@if ($orderProduct != null) {{ $orderProduct->id }} @endif)"
            type="button" class="btn btn-lg notThereBtn form-control">Edit
            Quantity
        </button>
    </div>
</div>
<div class="row">
    <div class="col-md-12 p-3">
        @if ($orderProduct != null)
            <a href="{{ action('Restaurant\KitchenController@incorrectLocation', [$orderProduct->id]) }}" type="button"
                class="btn btn-lg locationIncorrectBtn form-control">
                Incorrect Location
            </a>
        @else
            <button type="button" class="btn btn-lg locationIncorrectBtn form-control">
                Incorrect Location
            </button>
        @endif
    </div>
</div>
