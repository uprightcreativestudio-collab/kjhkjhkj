
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        <?php if (isset($_SESSION['flash_message'])): ?>
        const flashMessage = document.createElement('div');
        flashMessage.className = 'fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg animate-fade-in-out';
        flashMessage.textContent = '<?php echo $_SESSION['flash_message']; ?>';
        document.body.appendChild(flashMessage);
        setTimeout(() => {
            flashMessage.remove();
        }, 3000);
        <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes fade-in-out {
                0% { opacity: 0; transform: translateY(-20px); }
                10% { opacity: 1; transform: translateY(0); }
                90% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-20px); }
            }
            .animate-fade-in-out {
                animation: fade-in-out 3s forwards;
            }
        `;
        document.head.appendChild(style);
    </script>

