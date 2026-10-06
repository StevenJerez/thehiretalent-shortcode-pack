document.addEventListener("DOMContentLoaded", function () {
  const links = document.querySelectorAll(".custom-navbar a");
  const indicator = document.querySelector(".indicator");

  // Function to set indicator position and width based on the selected link
  function moveIndicator(element) {
    const linkRect = element.getBoundingClientRect();
    const navbarRect = document.querySelector(".custom-navbar").getBoundingClientRect();
    indicator.style.width = `${linkRect.width}px`;
    indicator.style.left = `${linkRect.left - navbarRect.left}px`;
  }

  // Initial setting for the indicator
  const activeLink = document.querySelector(".custom-navbar input:checked").parentElement;
  if (activeLink) moveIndicator(activeLink);

  links.forEach(link => {
    link.addEventListener("click", function (e) {
      const input = this.querySelector("input");
      if (input) input.checked = true;
      moveIndicator(this);
    });
  });
});
