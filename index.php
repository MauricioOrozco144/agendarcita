<?php
include 'config/db.php';
$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monica Orozco | Nails Professional</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { 
            --gold: #c5a059; 
            --soft-pink: #fdf5f6; 
            --warm-white: #ffffff;
            --text-main: #4a4a4a; 
            --border-light: #eee1e3;
        }
        
        body { font-family: 'Poppins', sans-serif; background-color: var(--warm-white); color: var(--text-main); scroll-behavior: smooth; }
        h1, h2, h3, h4, .navbar-brand { font-family: 'Playfair Display', serif; color: var(--text-main); }

        .navbar { background: var(--warm-white); border-bottom: 1px solid var(--border-light); padding: 15px 0; }
        .navbar-brand img { height: 45px; border-radius: 50%; border: 1px solid var(--gold); margin-right: 10px; }
        .nav-link { color: var(--text-main) !important; font-weight: 500; transition: 0.3s; }
        .nav-link:hover { color: var(--gold) !important; }

        .hero-section { padding: 80px 0; background-color: var(--soft-pink); }
        .profile-img { border-radius: 20px; border: 10px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 100%; }
        
        .section-title { position: relative; display: inline-block; margin-bottom: 30px; padding-bottom: 10px; }
        .section-title::after { content: ''; position: absolute; width: 60px; height: 2px; background: var(--gold); bottom: 0; left: 50%; transform: translateX(-50%); }

        .mision-vision-card { border: 1px solid var(--border-light); border-radius: 15px; background: white; transition: 0.3s; height: 100%; padding: 30px; }
        .mision-vision-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .mision-vision-card i { color: var(--gold); font-size: 2.5rem; }

        .gallery-img { width: 100%; height: 280px; object-fit: cover; border-radius: 15px; border: 5px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.4s; }
        .gallery-img:hover { transform: scale(1.03); border-color: var(--gold); }

        .booking-container { background: white; border: 1px solid var(--border-light); border-radius: 30px; padding: 40px; box-shadow: 0 15px 40px rgba(0,0,0,0.03); }
        .form-label { color: var(--text-main); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; }
        .form-control, .form-select { border: 1px solid var(--border-light); border-radius: 10px; padding: 12px; background: var(--soft-pink); }
        .form-control:focus { border-color: var(--gold); box-shadow: none; background: white; }
        
        .btn-confirm { background: var(--gold); color: white; border: none; padding: 18px; border-radius: 12px; font-weight: 600; text-transform: uppercase; transition: 0.4s; }
        .btn-confirm:hover { background: #b08d4b; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(197, 160, 89, 0.3); color: white; }

        .slot-item label { width: 100px; border-radius: 8px; border: 1px solid var(--border-light); transition: 0.2s; padding: 10px; }
        .btn-check:checked + label { background-color: var(--gold) !important; border-color: var(--gold) !important; color: white !important; }

        .spinner-gold { color: var(--gold); width: 3rem; height: 3rem; }

        .footer { background: var(--soft-pink); border-top: 1px solid var(--border-light); padding: 50px 0 20px; }
        .social-link { color: var(--gold); font-size: 1.5rem; margin: 0 12px; transition: 0.3s; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
            <img src="assets/img/logo.png" alt="Logo"> MONICA OROZCO
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link px-3" href="#nosotros">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#galeria">Trabajos</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#agendar">Citas</a></li>
                <li class="nav-item"><a class="nav-link fw-bold ms-lg-3" href="login.php" style="color: var(--gold) !important;"><i class="bi bi-person-lock me-1"></i>Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

<section id="nosotros" class="hero-section">...</section>

<section id="agendar" class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="booking-container">
                <h2 class="text-center mb-5 fw-bold">Reserva tu Turno</h2>
                
                <form action="guardar_cita.php" method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="cliente" class="form-control" placeholder="Nombre y Apellido" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp de Contacto</label>
                            <input type="tel" name="telefono" class="form-control" placeholder="Ej: 3101234567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">¿Qué día quieres venir?</label>
                            <input type="date" id="input_fecha" name="fecha_dia" class="form-control" 
                                   min="<?php echo $hoy; ?>" onchange="cargarHorarios()" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selecciona el Servicio</label>
                            <select id="select_servicio" name="id_servicio" class="form-select" onchange="cargarHorarios()" required>
                                <option value="">Elige una técnica...</option>
                                <?php
                                $servicios_db = mysqli_query($conn, "SELECT id, nombre_servicio, precio FROM servicios WHERE activo = 1 ORDER BY nombre_servicio ASC");
                                while($s = mysqli_fetch_assoc($servicios_db)):
                                ?>
                                    <option value="<?php echo $s['id']; ?>">
                                        <?php echo htmlspecialchars($s['nombre_servicio']); ?> - $<?php echo number_format($s['precio'], 0, ',', '.'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="text-center border-top pt-4">
                                <label class="form-label d-block mb-3" id="label_horarios">Horarios Disponibles</label>
                                <div id="contenedor_horarios" class="d-flex flex-wrap justify-content-center gap-2">
                                    <p class="text-muted small">Selecciona fecha y servicio para ver los turnos libres.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-5 text-center">
                            <button type="submit" id="btn_submit" class="btn btn-confirm px-5 w-100" disabled>
                                Confirmar mi Reserva
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
function cargarHorarios() {
    const id_serv = document.getElementById('select_servicio').value;
    const fecha = document.getElementById('input_fecha').value;
    const contenedor = document.getElementById('contenedor_horarios');
    const btnSubmit = document.getElementById('btn_submit');
    
    if (id_serv && fecha) {
        // Mostrar estado de carga
        contenedor.innerHTML = '<div class="spinner-border spinner-gold" role="status"></div>';
        btnSubmit.disabled = true;
        
        fetch(`get_slots.php?id=${id_serv}&fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                contenedor.innerHTML = ""; 
                
                // 1. Manejo de Días Bloqueados (Festivos/Descansos)
                if (data.bloqueado) {
                    contenedor.innerHTML = `
                        <div class="alert alert-warning border-0 p-4 w-100 shadow-sm">
                            <i class="bi bi-calendar-x fs-4 d-block mb-2 text-warning"></i>
                            <h6 class="fw-bold">Agenda no disponible</h6>
                            <p class="mb-0 small">${data.mensaje || 'Este día no hay servicio.'}</p>
                        </div>`;
                    return;
                }

                // 2. Manejo de día sin cupos libres
                if (!data.horarios || data.horarios.length === 0) {
                    contenedor.innerHTML = `
                        <div class="alert alert-danger border-0 p-4 w-100 shadow-sm">
                            <i class="bi bi-clock-history fs-4 d-block mb-2 text-danger"></i>
                            <h6 class="fw-bold">Sin turnos disponibles</h6>
                            <p class="mb-0 small">Prueba con otra fecha o servicio.</p>
                        </div>`;
                    return;
                }

                // 3. Renderizado de horarios exitoso
                data.horarios.forEach(hora => {
                    const idHTML = `h_${hora.replace(/:/g, '')}`;
                    const item = `
                        <div class="slot-item">
                            <input type="radio" class="btn-check" name="hora" id="${idHTML}" value="${hora}" onchange="document.getElementById('btn_submit').disabled = false;" required>
                            <label class="btn btn-outline-secondary" for="${idHTML}">${hora}</label>
                        </div>`;
                    contenedor.insertAdjacentHTML('beforeend', item);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                contenedor.innerHTML = '<p class="text-danger small">Error de conexión. Intente de nuevo.</p>';
            });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>