// This file contains JavaScript code for client-side functionality, including handling checkbox selections and making AJAX requests.

document.addEventListener('DOMContentLoaded', function() {
    const ingredientCheckboxes = document.querySelectorAll('input[type="checkbox"][name="ingredients[]"]');
    const searchButton = document.getElementById('search-button');
    const resultsContainer = document.getElementById('results');

    searchButton.addEventListener('click', function() {
        const selectedIngredients = Array.from(ingredientCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedIngredients.length > 0) {
            fetchRecipes(selectedIngredients);
        } else {
            resultsContainer.innerHTML = '<p>Please select at least one ingredient.</p>';
        }
    });

    function fetchRecipes(ingredients) {
        fetch('cari.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ingredients: ingredients })
        })
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            console.error('Error fetching recipes:', error);
            resultsContainer.innerHTML = '<p>Error fetching recipes. Please try again later.</p>';
        });
    }

    function displayResults(recipes) {
        resultsContainer.innerHTML = '';

        if (recipes.length === 0) {
            resultsContainer.innerHTML = '<p>No recipes found.</p>';
            return;
        }

        recipes.forEach(recipe => {
            const recipeElement = document.createElement('div');
            recipeElement.classList.add('recipe');
            recipeElement.innerHTML = `
                <h3>${recipe.title}</h3>
                <p>Ingredients: ${recipe.ingredients.join(', ')}</p>
            `;
            resultsContainer.appendChild(recipeElement);
        });
    }
});