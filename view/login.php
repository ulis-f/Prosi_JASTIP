<form class="Login-form" action="loginKlik" method="POST">
    <h1>Login</h1>
        <input type="username" class="login-username" autofocus="true" required="true" placeholder="Username"  name="nama"/>
        <input type="password" class="login-password" required="true" placeholder="Password" minlength="8" id="password" name="password"/>
        
        <input type="submit" name="Login" value="Login" class="login-submit"/>
</form>
<button onclick="daftar()">Register</button>

<script>
    function daftar(){
        location.href = "register";
    }
</script>
