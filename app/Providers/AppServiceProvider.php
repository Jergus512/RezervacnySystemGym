use Illuminate\Pagination\Paginator;

public function boot()
{
    Paginator::defaultView('vendor.pagination.custom');
    // ...existing code...
}
