# Technical Decisions & Architecture

### 1. Technical Decisions:

**Scoring:** Using nested `if-else` blocks for lead scoring is unmaintainable as new rules are added.

I implemented the **Strategy Pattern** via the `ScoringRuleInterface` and the `LeadScorer` engine. 
- Each rule (`BudgetRule`, `SourceRule`, `EmailDomainRule`, etc.) is encapsulated in its own class, adhering to the Single Responsibility Principle.
- Adding a new rule is as simple as creating a new class and registering it in the `AppServiceProvider`.
- This avoids modifying the core scoring logic, adhering to the Open/Closed Principle.

**Choice of Notification Channel:**

The Laravel's internal notification system is used for its robustness and multichannel support.
- `database` channel is used to keep an audit trail of triggered notifications for UI display.
- `mail` channel simulates dispatching the alert to a sales team.
- A custom `webhook` channel dispatches the payload to the external URL.

### 2. Real-World Scenarios Management

**A) Handling Incomplete Data:**
- Validation rules require the `name` field, but only require *at least one* of `email` or `phone` using Laravel's `required_without` rule. 
- If both are missing, it returns a `422 Unprocessable Content`.

**B) Deduplication Strategy (Upserting):**
- When a lead with an existing email or phone arrives, we use an **Upsert Strategy**.
- We fetch the existing lead, update its properties (budget, source, additional data), and **re-calculate** the score and priority.
- This ensures sales reps always have the freshest context, and subsequent high-value activities (like increased budget) bump the lead's priority back to Hot.

**C) Webhook Failures (Resiliency):**
- The custom webhook channel uses a `try...catch` block around the `Http::post()` call.
- Failures are caught and logged, preventing the main application thread from crashing.

### 3. What If I Had More Time?

**A) Improvements:**
- Adding Rate limiting feature to prevent DDoS attacks and spam.
- Implement exponential backoff, retry limits, and timeout configurations for the WebhookChannel. If a partner API is down, Laravel should try again in 5 mins, 15 mins, etc., before moving the job to a failed jobs table (Dead Letter Queue).
- Use different ways to prevent duplicate entries if two requests for the same lead arrive at the exact same millisecond.

**B) Scaling:**
- Use the database advanced features to like indexing, caching and ... .
- Use separated cache systems like Redis and other stuff .
- Log and monitor the entire system using tools like Sentry or Elastic and ... .
- Create a real-time dashboard using websocket.
