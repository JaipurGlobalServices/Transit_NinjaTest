</div>

<?php include './partials/footer.php' ?>
</main>

<?php include './partials/scripts.php' ?>
<script> 
document.addEventListener("DOMContentLoaded", function() { 
    var logo = document.querySelector(".dark-logo"); 
    // Replace .problematic-logo with the actual class or ID of your image 
    if (logo) { logo.style.left = "0"; logo.style.position = "relative"; } 
    }); 
</script>
<!-- 
<script> document.addEventListener("DOMContentLoaded", function() { function fixLogoStyles() { var logo = document.querySelector(".problematic-logo"); // Replace .problematic-logo with the actual class or ID of your image if (logo) { logo.style.left = "0"; logo.style.position = "relative"; } } // Initial fix fixLogoStyles(); // Observe changes in the body var observer = new MutationObserver(function(mutations) { mutations.forEach(function(mutation) { fixLogoStyles(); }); }); // Start observing the body for attributes changes observer.observe(document.body, { attributes: true, childList: true, subtree: true }); }); </script>
-->
</body>

</html>