@extends('layouts.admin')

@section('title', 'Data Masyarakat')


@section('content')
    <a href="{{route('admin.resident.create')}}" class="btn btn-primary mb-3">Tambah Data</a>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Masyarakat</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Email</th>
                            <th>Nama</th>
                            <th>Avatar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($residents as $resident)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $resident->user->email}}</td>
                                <td>{{ $resident->user->name}}</td>
                                <td>
                                    <img src="{{asset('storage/'. $resident->avatar)}}" alt="avatar" width="100">
                                </td>
                                <td>
                                    <a href="{{route('admin.resident.edit', $resident->id)}}" class="btn btn-warning">Edit</a>

                                    <a href="{{route('admin.resident.show', $resident->id)}}" class="btn btn-info">Show</a>

                                    <form id="delete-form-{{ $resident->id }}" action="{{ route('admin.resident.destroy', $resident->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ $resident->id }}')">Delete</button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
