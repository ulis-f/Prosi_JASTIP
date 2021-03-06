<style>
    figure:hover{
        transform: scale(2.4); 
    }

    body{
        background-color:white;
    }
    .column {
        width: 58%;
        float: left;
    }

    .column1 {
        width: 38%;
        float: right;
    }
</style>

<ul class="nav" style="margin-top:2%; background-color:white; margin-left:3%;">
    <li class="nav-item w3-text-theme w3-text-theme">
        <a class="nav-link w3-text-theme" href="homeAdmin" style="height: 100%;">Post Trip</a>
    </li>
    <li class="nav-item w3-text-theme w3-text-theme">
        <a class="nav-link w3-text-theme" href="lengkapDaftar" style="height: 100%;">Lengkapi Pendaftaran</a>
    </li>
    <li class="nav-item w3-text-theme w3-text-theme">
        <a class="nav-link text-center " href="#"  style="height: 100%; border-bottom: 4px solid #6699cc; ">Form Barang</a>
    </li>
    <li class="nav-item w3-text-theme">
        <a class="nav-link" href="pembayaranAdmin" style="height: 100%;">Pembayaran</a>  
    </li>
    <li class="nav-item w3-text-theme">
        <a class="nav-link" href="pengirimanUang" style="height: 100%;">Pengiriman Uang</a>
    </li>
    <li class="nav-item w3-text-theme">
        <a class="nav-link" href="laporan" style="height: 100%;">Laporan</a>
    </li>
</ul>

<fieldset class="" style="margin-left:2%; margin-right:2%; border: #6699cc 1px solid">
    <p class="" style="width:100%;border-bottom:1px solid #6699cc"> 
        Sekarang anda berada di halaman :
        <a class="" href="persetujuanBarang" style="text-decoration:none; ">Form Barang</a>
        <i class="fa fa-angle-double-right" style=""></i>  
        <a class="" href="#" style="text-decoration:none; color:#6699cc "><b>Detail Form Barang</b></a>
    </p> 
    <div class="container">
        <div class="w3-card-4 w3-white" style="margin: auto; padding: 50px; height: 600px; margin-top: 2%;">
            <h3><b>Persetujuan Barang</b></h3> <br>
            <div class="column">
                <form action="verifikasiBarang" method="POST">
                    <table>
                        <tr>
                            <td><label for="namaBarang">Nama Barang</label></td> 
                            <td>:</td>
                            <?php
                            foreach($result as $key=>$value){
                                echo"<input type='hidden' name='namaBarang' value='".$value->namaBarang."'>";
                                echo "<td>".$value->namaBarang."</td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label for="kategoriBarang">Kategori Barang</label></td>
                            <td>:</td>
                            <?php
                            foreach($result as $key=>$value){
                            echo "<td>".$value->namaKategori."</td>";
                            }
                            ?>
                        </tr>
                        <?php
                        echo"<input type='hidden' name='market' value='".$value->statusBarang."'>";
                        if($value->statusBarang=="onPending"){
                            echo "<tr>
                            <td><label for='hargaBarang'>Harga Barang</label></td>
                            <td>:</td>";
                            
                            foreach($result as $key=>$value){
                            echo "<td>".$value->hargaBarang."</td>";
                            }
                            
                            echo "</tr>
                            <tr>
                                <td><label for='trip'>Trip</label></td>
                                <td>:</td>
                                ";
                                foreach($trip as $key=>$value){
                                    echo "<td>".$value->kotaAwal."->".$value->kotaTujuan."</td>";
                                } 
                                
                            echo "</tr>";
                        }  
                        ?>
                        
                        <tr>
                            <td><label for="deskripsiBarang">Deskripsi Barang</label></td>
                            <td>:</td>
                            <?php
                            foreach($result as $key=>$value){
                            echo "<td>".$value->deskripsiBarang."</td>";
                            }
                            ?>
                        </tr>
                    </table>
                    
                    <br>
                    <label for="gambarBarang">Gambar Barang</label>  
                    <?php
                    foreach($result as $key=>$value){
                    echo"<figure><img src='image/market/".$value->gambarBarang."'width=250px height=150px></figure><br>"; 
                    }
                    ?>
                </div>
                <div class="column1">
                    <input type='radio' id='verified' name='verified' value='verified'>Verified<br>
                    <input type='radio' id='unverified' name='unverified' value='unverified'>Unverified<br>      
                    <br>
                    <label for='note'>Note</label> <br>
                    <textarea name='' id='' cols='40' rows='5'></textarea>
                    <br><br><br><br><br>
                    <input type='submit' class='w3-btn w3-right w3-theme' style="width:100%;" value='Submit'>  
                </div>
            </form>    
        </div>
    </div>
</fieldset>