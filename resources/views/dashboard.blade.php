<script>
    window.location.href = "{{ auth()->check() && auth()->user()?->hasRole('Administrador') ? '/admin' : '/' }}";
</script>
