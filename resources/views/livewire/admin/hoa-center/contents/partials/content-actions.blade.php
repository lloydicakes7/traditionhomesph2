{{-- One page's actions, as the pages table and its mobile cards both render
     them. `$actionKey` namespaces the dropdown's id: the desktop row and the
     mobile card are both on the page, and only one of them is ever visible. --}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="contentActions{{ $actionKey }}{{ $content->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>

    <div class="dropdown-menu dropdown-menu-right"
        aria-labelledby="contentActions{{ $actionKey }}{{ $content->id }}">
        <a class="dropdown-item" href="{{ $content->url }}" target="_blank" rel="noopener">
            <i class="fas fa-fw fa-external-link-alt mr-1"></i> View page
        </a>

        @can('update', \App\Models\Content::class)
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#contentFormModal" data-id="{{ $content->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit
            </a>
        @endcan

        @can('delete', \App\Models\Content::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#contentDeleteModal" data-id="{{ $content->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
