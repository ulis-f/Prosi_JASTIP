<link rel="stylesheet" href="http://localhost:80/prosi_JASTIP/view/layout/style/style.css">
<div class="container">
        <div class="w3-card-4 w3-white" style="width:60%; margin: auto; padding: 50px; height: 750px; ">
            <h2>Pendaftaran Tambahan</h2>
            <br>
            <br>
            <br>
            <form action="lengkapKlik" method="POST" enctype="multipart/form-data">
                <div class="container"> 
                    <label for="nik"><b>NIK</b></label>
                    <input type="text" placeholder="" name="nik">
                    <br>
                    <label for="bank"><b>Nama Bank</b></label>
                    <br>
                    <br>
                    <select id="bank" name="namaBank"> 
                        <option value="BCA">BCA</option>
                        <option value="BRI">BRI</option>
                        <option value="BNI">BNI</option>
                        <option value="OCBC">OCBC</option>
                        <option value="MANDIRI">MANDIRI</option>
                    </select>
                    <br>
                    <br>
                    <label for="noRek"><b>No. Rekening</b></label>
                    <br>
                    <input type="text" placeholder="" name="noRek">
                    <br>
                    <h4>Upload Foto KTP</h4>
                    <input type='file' name='fotoKTP' accept='image/*'>  
                    <br>
                    <h4>Upload Foto Swafoto</h4>
                    <input type='file' name='fotoSelfie' accept='image/*'>  
                    <br>
                     
                </div>

                <br>
                <br>
                <br> 

                <div class="w3-right" style="padding-top: 100px;"> 
                    <input type="submit" value="subimt" class="w3-btn" style="background-color:#b74449; color: white;">
                </div>

            </form>
        </div>
    </div>