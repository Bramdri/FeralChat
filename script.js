document.addEventListener("DOMContentLoaded", function() {
  const steps = document.querySelectorAll(".form-step");
  const progressBar = document.getElementById("progressBar");
  const nextButtons = document.querySelectorAll(".next-btn");
  const prevButtons = document.querySelectorAll(".prev-btn");
  let currentStep = 0;

  // Update the form step and progress bar
  function updateStep() {
      steps.forEach((step, index) => {
          step.classList.toggle("active", index === currentStep);
      });
      progressBar.style.width = ((currentStep + 1) / steps.length) * 100 + "%";
      updateNextButtonState();
  }

  // Enable/disable the "Next" button based on input field validation
  function updateNextButtonState() {
      const currentStepInputs = steps[currentStep].querySelectorAll("input, textarea");
      const nextButton = steps[currentStep].querySelector(".next-btn");

      // Enable the "Next" button if all required fields are filled out
      const allFieldsFilled = Array.from(currentStepInputs).every(input => input.checkValidity());
      nextButton.disabled = !allFieldsFilled;
  }

  // Enable/disable the "Next" button whenever an input field changes
  document.querySelectorAll("input, textarea").forEach(input => {
      input.addEventListener("input", updateNextButtonState);
  });

  // Handle "Next" button click
  function nextStep() {
      if (currentStep < steps.length - 1) {
          currentStep++;
          updateStep();
      }
  }

  // Handle "Previous" button click
  function prevStep() {
      if (currentStep > 0) {
          currentStep--;
          updateStep();
      }
  }

  // Event listeners for the "Next" and "Previous" buttons
  nextButtons.forEach(button => {
      button.addEventListener("click", nextStep);
  });

  prevButtons.forEach(button => {
      button.addEventListener("click", prevStep);
  });

  // Initialize the form (load the first step)
  updateStep();
});