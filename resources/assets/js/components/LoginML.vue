<template>
    <div class="card text-center">
        <div class="card-header">
            Featured
        </div>
        <div class="card-body">
            <h5 class="card-title">Special title treatment</h5>
            <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
            <a href="#" v-on:click="login" class="btn btn-primary">Iniciar Sesión</a>
        </div>
        <div class="card-footer text-muted">
            2 days ago
        </div>
    </div>
</template>
    <script>
        export default {
            methods:{
                login(){
                     MELI.login(function() {
                        MELI.get("/users/me", {}, function(data) {
                     	    console.log("Hello " + data[2].first_name);
                        });
                    });
                    //console.log("Login!")
                }
            }
        }
    </script>