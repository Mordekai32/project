document.addEventListener("DOMContentLoaded", () => {
  const menuItems = [
    { name: "Grilled Steak", desc: "Served with garlic butter", price: "$25" },
    { name: "Spicy Chicken Wings", desc: "6 pcs with BBQ sauce", price: "$12" },
    { name: "Classic Burger", desc: "With fries and cheese", price: "$15" },
    { name: "Margarita Pizza", desc: "Fresh mozzarella & basil", price: "$18" },
  ];

  const menuContainer = document.getElementById("menu-items");
  menuItems.forEach(item => {
    const div = document.createElement("div");
    div.className = "bg-white p-6 rounded-lg shadow hover:shadow-lg transition";
    div.innerHTML = `
      <h3 class="text-2xl font-bold text-blue-700 mb-2">${item.name}</h3>
      <p class="text-gray-600">${item.desc}</p>
      <span class="block mt-4 text-xl font-semibold text-blue-800">${item.price}</span>
    `;
    menuContainer.appendChild(div);
  });

  const form = document.getElementById("reservationForm");
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Reservation submitted! We'll get back to you soon.");
    form.reset();
  });
});
