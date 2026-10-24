    <!-- Footer -->
    <footer style="background: #060608; border-top: 1px solid var(--border-color); padding: 40px 0 24px; margin-top: 40px;">
        <div class="container">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 30px; margin-bottom: 30px;">
                <div style="max-width: 320px;">
                    <img src="logo.png" alt="SmashIT Burgers" style="height: 48px; margin-bottom: 12px;">
                    <p style="color: var(--text-muted); font-size: 0.88rem;">Hamburguesas aplastadas al momento con carne 100% de novillo seleccionada, ingredientes frescos y sabor auténtico.</p>
                </div>
                
                <div>
                    <h4 style="font-family: var(--font-heading); color: var(--text-main); margin-bottom: 12px;">Horarios de Atención</h4>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">Lunes a Jueves: 19:00 - 00:00</p>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">Viernes a Domingo: 19:00 - 02:00</p>
                </div>

                <div>
                    <h4 style="font-family: var(--font-heading); color: var(--text-main); margin-bottom: 12px;">Contacto & Ubicación</h4>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">📍 Av. Principal #1234, Ciudad</p>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">📞 +54 9 11 5555-SMASH</p>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; text-align: center; color: var(--text-dim); font-size: 0.82rem;">
                © <?php echo date('Y'); ?> SmashIT Burgers. Todos los derechos reservados. Sistema modular extensible desarrollado en JS, PHP y SQL.
            </div>
        </div>
    </footer>

    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Application Script -->
    <script src="assets/js/app.js"></script>
</body>
</html>
