@foreach ($leads as $lead)
    <x-sales.kanban-card :lead="$lead" :kanban-users="$kanbanUsers ?? collect()" />
@endforeach