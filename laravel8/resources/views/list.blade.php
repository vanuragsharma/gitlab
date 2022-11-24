<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <title>Document</title>
</head>
<body>
    <div class="container" style="margin-top:20px;">
    @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
    @endif
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Email</th>
                <th>Description</th>  
            </tr>
            </thead>
            <tbody>
            <?php $i=0; ?>    
            @foreach($datas as $key => $data)  
            <tr>
                <td>{{$key+1}}</td>
                <td>{{$data['email']}}</td>
                <td>{{$data['description']}}</td>    
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>    
</body>
</html>