# Product Catalog Application

## Goal

Build a simple product catalog web application that allows users to browse, filter, and search for products.

## Requirements

The product catalog must allow users to:

- Filter by category, manufacturer and price range
- Search by name, manufacturer or description (full-text search)
- Filter category-specific attributes:
  - Batteries: capacity range
  - Solar panels: power output
  - Connectors: type

**Note:** You don't have to implement any other functionalities like login or products editor.

## Technical Requirements

### Backend

- Use Laravel
- You may use any database and similar tools for storing products

### Data

- Use only the sample data from provided CSV files

### Frontend

- Any approach is acceptable, including generating it with AI or using no-code tools
- UI design will not be evaluated — focus on the quality of backend

## Description

Three CSV files are provided, representing product data for the photovoltaic industry:

- Solar panels
- Batteries
- Connectors

### CSV Structure

Each CSV file includes the following columns:

- `name`
- `manufacturer`
- `price`
- `description`

Plus, they include category-specific columns:

- **Batteries:** `capacity`
- **Solar panels:** `power_output`
- **Connectors:** `connector_type`

## Evaluation Criteria

We will evaluate based on:

- Code structure and quality
- Correct and efficient filtering/search logic
- DB design and design patterns

