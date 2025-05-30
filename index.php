<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Charming Smile Dental Clinic</title>
  <?php require_once 'db/head.php' ?>

  <style>
    #home {
      width: 100%;
      height: 90vh;
      background: url("img/home.jpg") top center;
      background-size: cover;
      background-attachment: fixed;
      
    }

    @media only screen and (max-width: 768px) {

      #home {
        height: 70vh;
              background: url("img/home.jpg") right center ;
      }

    }

    #home .container {
      position: relative;
    }

    #home h1 {
      margin: 0;
      font-weight: 700;
      color: white;
    }

    .top-header {
      background-color: #000000;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: white;
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      box-sizing: border-box;
      z-index: 100;
    }

    .left-section {
        display: flex;
        align-items: center; /* This will vertically align the logo and text */
        gap: 10px;
    }

    .logoDental {
        font-weight: 900;
        font-size: 22px;
        line-height: 1; /* Remove extra line height */
        white-space: nowrap; /* Prevent text from wrapping */
    }

    .logo {
      width: 55px;
      height: 50px;
      background-color: #d99e9e; /* Light pink for profile icon */
      border-radius: 20%; /* Make profile icon circular */
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 20px; /* Font size for initials inside the profile icon */
      margin-right: 20px;
    }

  </style>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="">
    <div class="container d-flex align-items-center">
      <div class="top-header">
        <div class="left-section">
            <img src="img/pfp.jpg" alt="Profile Picture" class="logo">
            <div class="logoDental">CHARMING SMILE<br>DENTAL CLINIC</div>
        </div>

<!--       <h1 class="logo me-auto d-block d-md-none"><a href="index.php">CSDC</a></h1> -->
      <nav id="navbar" class="navbar order-last order-lg-0">
        <ul>
          <li><a class="nav-link scrollto active" href="#home">Home</a></li>
          <li><a class="nav-link scrollto" href="#services">Services</a></li>
          <li><a class="nav-link scrollto" href="#contact">Clinic info</a></li>
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->
    </div>
  </header><!-- End Header -->

  <!-- ======= Home Section ======= -->
  <section id="home" class="d-flex align-items-center">
    <div class="container">
      <h1>Have a teeth problem?</h1>
      <h2 class="text-white">We got you covered with our quality and affordable dental services.🦷</h2>
      <div class="w-100 d-flex justify-content-center justify-content-md-start mt-5">
        <a href="patient/patLogin.php" class="signup-btn">Book <span class="d-none d-md-inline">an Appointment
          </span>now!</a>
      </div>
    </div>
  </section><!-- End Home -->


  <!-- ======= Services Section ======= -->
  <section id="services" class="services">
    <div class="container">

      <div class="section-title">
        <h2>Services Offered</h2>
      </div>

      <div class="row">
        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4><a href="">Cosmetic Dentistry</a></h4>
            <p>Teeth Whitening<br>Direct & Indirect Veneers</p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4><a href="">Dental X-ray</a></h4>
            <p>Periapical</p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4><a href="">Endodontics</a></h4>
            <p>Root Canal Therapy</p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4><a href=""> General Services</a></h4>
            <p>Oral Prophylaxis (Cleaning)
              <br>Tooth Filling (Pasta)
              <br>Simple Extraction (Bunot)
              <br>Oral Rehabilitation
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4>Oral Surgery</h4>
            <p>Simple Extraction
              <br>Odontectomy (Removal of Impacted Wisdom Tooth)
              <br>Frenectomy
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4>Orthodontics</h4>
            <p>Conventional Metal Braces
              <br>Retainers
              <br>Mouth Guard
              <br>Bite Plane / Inclined Plane
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4>Pedodontics</h4>
            <p>Pit and Fissure Sealant
              <br>Fluoride Application
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4>Periodontics</h4>
            <p>Gingivitis<br>Gum Treatment</p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-center mt-4">
          <div class="icon-box">
            <div class="icon"><i class="fas fa-tooth"></i></div>
            <h4>Prosthodontics</h4>
            <p>Complete Denture (Pustiso)
              <br>Removable Partial Denture (Thermosen, Ivocap, Flexible, Acrylic)
              <br>Jacket Crown/Fixed Bridge (Zirconia, All Porcelain (EMAX), Porcelain Fused to Metal, Ceramage,
              Plastic)
            </p>
          </div>
        </div>

      </div>
    </div>
  </section><!-- End Services Section -->


  <!-- ======= Info Section ======= -->
  <section id="contact" class="contact">

    <div class="section-title">
      <h2>Clinic Info</h2>
    </div>
    <div class="container">
      <div class="col-lg-4">
        <div class="info">

          <div class="hours">
            <i class="fa fa-clock"></i>
            <h4>Clinic Hours</h4>
            <p>9am-6pm (Mon, Wed-Sun)</p>
          </div>
          <br>
          <div class="address">
            <a
              href="https://www.google.com/maps/place/Charming+Smile+Dental+Clinic/@14.640419,121.0071371,17z/data=!3m1!4b1!4m6!3m5!1s0x3397b700d17622eb:0x96cea99ca013e7d7!8m2!3d14.640419!4d121.009712!16s%2Fg%2F11rh4b78zg?entry=ttu&g_ep=EgoyMDI1MDEyOS4xIKXMDSoASAFQAw%3D%3D"><i
                class="bi bi-geo-alt"></i></a>
            <h4>Location:</h4>
            <p>2nd floor, #4 Corumi St., Brgy. Masambong, Del Monte Avenue, Quezon City, Philippines</p>
          </div>
          <div class="facebook">
            <a href="https://www.facebook.com/CharmingSmileDentalClinic"><i class="bi bi-facebook"></i></a>
            <h4>Facebook</h4>
            <p>Charming Smile Dental Clinic</p>
          </div>
          <br>
          <div class="phone">
            <i class="bi bi-phone"></i>
            <h4>Call:</h4>
            <p>0916-846-4995 / 0999-821-1819 </p>
          </div>
        </div>
      </div>
    </div>
  </section><!-- End Contact Section -->

  <div id="preloader"></div>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>
  <?php require_once 'db/script.php' ?>
</body>

</html>