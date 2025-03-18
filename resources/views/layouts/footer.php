                </div>
                <!-- End Page Content -->
            </div>
            <!-- End Main Content -->
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('.table').DataTable();
            
            // Toggle sidebar on mobile
            $('.navbar-toggler').on('click', function() {
                $('.sidebar').toggleClass('show');
            });
        });
    </script>
</body>
</html> 