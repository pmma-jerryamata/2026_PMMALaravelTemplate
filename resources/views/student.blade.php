<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student</title>
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Create Student Account</h1>
        
        <!-- Display success message if any -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}<br>
                <a href="{{ route('index') }}">Proceed to Job Request Portal. Click Here!</a> 
            </div>
        @endif

        <!-- Display validation errors if any -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @if ($error == "The email has already been taken.")
                            <a href="{{ route('index') }}">Proceed to Job Request Portal, and use your existing email. Click Here!</a>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form to create a new student -->
        <form action="{{ route('student.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label"><strong>Name</strong> (LASTNAME, FIRSTNAME)</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email</strong> (SERIAL_No@cdt.pmma.edu.ph)</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <input type="hidden" class="form-control" id="department_id" name="department_id" value="44">
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('student') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JS for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>