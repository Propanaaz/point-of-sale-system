@extends('layouts.admin')

@section('title', 'Import Products')
@section('content-header', 'Import Products')

@section('content')

<div class="card">
    <div class="card-body">
<a href="{{ route('products.template.download') }}"
   class="btn btn-info mb-3">
    Download CSV Template
</a>
        <form action="{{ route('products.import') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            {{-- CSV FILE --}}
            <div class="form-group">

                <label>CSV File</label>

                <input type="file"
                       name="csv_file"
                       class="form-control @error('csv_file') is-invalid @enderror"
                       accept=".csv">

                @error('csv_file')
                    <span class="invalid-feedback d-block">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

            </div>

            {{-- PRODUCT IMAGES --}}
            <div class="form-group mt-4">

                <label>Product Images</label>

                <input type="file"
                       name="images[]"
                       class="form-control"
                       multiple>

                <small class="text-muted">
                    Upload all product images here.
                    Image names must match CSV image column.
                </small>

            </div>

            <button type="submit" class="btn btn-success mt-3">
                Upload Products
            </button>

        </form>

        <hr>

        <h5>CSV Example</h5>

<pre>
name,description,image,barcode,price,purchase_price,quantity,status
HP Laptop,Core i7 Laptop,hp.jpg,111222,1200,900,5,1
Dell Mouse,Wireless Mouse,mouse.jpg,333444,25,10,50,1
</pre>

    </div>
</div>

@endsection
