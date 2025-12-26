<script>
function calculatePrice() {
    const court = document.getElementById('court_id');
    const start = document.getElementById('start_time').value;
    const end = document.getElementById('end_time').value;
    const priceInput = document.getElementById('total_price');

    if (!start || !end) return;

    const pricePerHour = Number(
        court.options[court.selectedIndex].dataset.price
    );

    const s = new Date(`1970-01-01T${start}:00`);
    const e = new Date(`1970-01-01T${end}:00`);

    if (e <= s) {
        priceInput.value = '';
        return;
    }

    const hours = (e - s) / 3600000;
    priceInput.value = Math.round(hours * pricePerHour);
}

document.addEventListener('DOMContentLoaded', () => {
    calculatePrice();

    document.getElementById('court_id').addEventListener('change', calculatePrice);
    document.getElementById('start_time').addEventListener('change', calculatePrice);
    document.getElementById('end_time').addEventListener('change', calculatePrice);
});
</script>
