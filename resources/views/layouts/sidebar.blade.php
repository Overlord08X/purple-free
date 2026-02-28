<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <!-- partial:partials/_sidebar.html -->
  <nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
      <li class="nav-item nav-profile">
        <a href="#" class="nav-link">
          <div class="nav-profile-image">
            <img src="{{ asset('assets/images/faces/face1.jpg') }}" alt="profile" />
            <span class="login-status online"></span>
            <!--change to offline or busy as needed-->
          </div>
          <div class="nav-profile-text d-flex flex-column">
            <span class="font-weight-bold mb-2">David Grey. H</span>
            <span class="text-secondary text-small">Project Manager</span>
          </div>
          <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard.index') }}">
          <span class="menu-title">Dashboard</span>
          <i class="mdi mdi-home menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#Kategori" aria-expanded="false" aria-controls="Kategori">
          <span class="menu-title">Kategori</span>
          <i class="mdi mdi-format-list-bulleted menu-icon"></i>
        </a>
        <div class="collapse" id="Kategori">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('kategori.index') }}">Kategori</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#Buku" aria-expanded="false" aria-controls="Buku">
          <span class="menu-title">Buku</span>
          <i class="mdi mdi-book menu-icon"></i>
        </a>
        <div class="collapse" id="Buku">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('buku.index') }}">Buku</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#PDF" aria-expanded="false" aria-controls="PDF">
          <span class="menu-title">PDF</span>
          <i class="mdi mdi-file-pdf-box menu-icon"></i>
        </a>
        <div class="collapse" id="PDF">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('pdf.sertifikat') }}">Sertifikat</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('pdf.undangan') }}">Undangan</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#TagHarga" aria-expanded="false" aria-controls="TagHarga">
          <span class="menu-title">Tag Harga</span>
          <i class="mdi mdi-printer menu-icon"></i>
        </a>
        <div class="collapse" id="TagHarga">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('tagHarga.index') }}">Tag Harga</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="docs/documentation.html" target="_blank">
          <span class="menu-title">Documentation</span>
          <i class="mdi mdi-file-document-box menu-icon"></i>
        </a>
      </li>
    </ul>
  </nav>