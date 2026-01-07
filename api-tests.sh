#!/bin/bash

# Task Fiend API Test Suite
# =========================
# These cURL commands can be:
# 1. Run directly from terminal (chmod +x api-tests.sh && ./api-tests.sh)
# 2. Imported into Postman (Postman can import cURL commands)
# 3. Used as reference for testing
#
# SETUP:
# Replace YOUR_API_KEY with your actual API key (tfk_xxxxx)
# Replace localhost:8000 with your app URL if different

BASE_URL="http://localhost:8000/api"
API_KEY="YOUR_API_KEY"

echo "======================================"
echo "Task Fiend API Test Suite"
echo "======================================"
echo ""

# ======================
# 1. AUTHENTICATION TESTS
# ======================

echo "1. TEST: Missing API Key (should fail with 401)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "Test task"}' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "2. TEST: Invalid API Key (should fail with 401)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer tfk_invalid_key_12345" \
  -d '{"name": "Test task"}' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "3. TEST: Valid API Key with simple task (should succeed)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{"name": "Test task with valid auth"}' \
  -w "\nHTTP Status: %{http_code}\n\n"

# ======================
# 2. TASK CREATION TESTS
# ======================

echo "4. TEST: Create simple task"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Buy groceries"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "5. TEST: Create task with description"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Write report",
    "description": "Q4 financial report with detailed analysis"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "6. TEST: Create task with specific date"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Team meeting",
    "datetime": "2025-12-31T14:00:00Z"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "7. TEST: Create task with natural language date (daily)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Morning standup daily"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "8. TEST: Create task with natural language date (weekly)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Team sync every Monday"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "9. TEST: Create task with natural language date (monthly)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Review finances monthly"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "10. TEST: Create task with natural language date (specific day)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Backup server every Friday"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "11. TEST: Create task with project_id (replace with valid project ID)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task in project",
    "project_id": 1
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "12. TEST: Create task with tags (replace with valid tag IDs)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Tagged task",
    "tag_ids": [1, 2]
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "13. TEST: Create task with assignees (replace with valid user IDs)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Assigned task",
    "assignee_ids": [1, 2]
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "14. TEST: Create task with explicit recurrence pattern"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Weekly review",
    "recurrence_pattern": "weekly"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "15. TEST: Create complex task with all fields"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Complex task",
    "description": "This task has all possible fields populated",
    "datetime": "2025-12-31T10:00:00Z",
    "project_id": 1,
    "recurrence_pattern": "weekly",
    "tag_ids": [1],
    "assignee_ids": [1]
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

# ======================
# 3. VALIDATION ERROR TESTS
# ======================

echo "16. TEST: Missing required name field (should fail with 422)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "description": "Task without name"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "17. TEST: Invalid datetime format (should fail with 422)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Invalid date task",
    "datetime": "not-a-date"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "18. TEST: Invalid project_id (should fail with 422)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task with invalid project",
    "project_id": 99999
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "19. TEST: Invalid tag_ids (should fail with 422)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task with invalid tags",
    "tag_ids": [99999]
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "20. TEST: Invalid assignee_ids (should fail with 422)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task with invalid assignees",
    "assignee_ids": [99999]
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

# ======================
# 4. GET TASKS ON DAY TESTS
# ======================

echo "21. TEST: Get tasks on today"
TODAY=$(date +%Y-%m-%d)
curl -X GET "${BASE_URL}/tasks/on/${TODAY}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "22. TEST: Get tasks on specific future date"
curl -X GET "${BASE_URL}/tasks/on/2025-12-31" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "23. TEST: Get tasks on specific past date"
curl -X GET "${BASE_URL}/tasks/on/2025-01-01" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "24. TEST: Get tasks with invalid date format (should fail with 400)"
curl -X GET "${BASE_URL}/tasks/on/invalid-date" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "25. TEST: Get tasks without authentication (should fail with 401)"
curl -X GET "${BASE_URL}/tasks/on/${TODAY}" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\n\n"

# ======================
# 5. GET COMPLETED TASKS TESTS
# ======================

echo "26. TEST: Get completed tasks on today"
curl -X GET "${BASE_URL}/tasks/completed/${TODAY}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "27. TEST: Get completed tasks on specific date"
curl -X GET "${BASE_URL}/tasks/completed/2025-12-25" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "28. TEST: Get completed tasks with invalid date format (should fail with 400)"
curl -X GET "${BASE_URL}/tasks/completed/not-a-date" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "29. TEST: Get completed tasks without authentication (should fail with 401)"
curl -X GET "${BASE_URL}/tasks/completed/${TODAY}" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\n\n"

# ======================
# 6. EDGE CASE TESTS
# ======================

echo "30. TEST: Create task with very long name"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "This is a very long task name that contains exactly two hundred and fifty five characters to test the maximum length validation on the name field. This should succeed if the validation accepts up to 255 characters but would fail if it was longer than that limit."
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "31. TEST: Create task with name exceeding 255 chars (should fail)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "This is a very long task name that contains more than two hundred and fifty five characters and should fail validation because it exceeds the maximum length allowed by the database schema and validation rules set up in the Laravel application for the name field of tasks."
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "32. TEST: Create task with empty string name (should fail)"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": ""
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "33. TEST: Create task with special characters"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Special chars: !@#$%^&*()_+-=[]{}|;:,.<>?/~`"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "34. TEST: Create task with Unicode/emoji"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Meeting with team 🚀 discuss plans 📋"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "35. TEST: Create task with null values"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task with nulls",
    "description": null,
    "datetime": null,
    "project_id": null
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "36. TEST: Get tasks on leap year date"
curl -X GET "${BASE_URL}/tasks/on/2024-02-29" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "37. TEST: Create task with natural language - today"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task due today"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "38. TEST: Create task with natural language - tomorrow"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task due tomorrow"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "39. TEST: Create task with natural language - weekday"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Task every weekday"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "40. TEST: Create task with natural language - weekend"
curl -X POST "${BASE_URL}/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${API_KEY}" \
  -d '{
    "name": "Relax every weekend"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n"

echo ""
echo "======================================"
echo "All tests completed!"
echo "======================================"
echo ""
echo "NOTES:"
echo "- Replace YOUR_API_KEY with your actual API key"
echo "- Some tests expect failures (401, 422, 400) - this is correct"
echo "- Tests with project_id, tag_ids, assignee_ids may need valid IDs"
echo "- Import these commands into Postman for easier testing"
echo ""
