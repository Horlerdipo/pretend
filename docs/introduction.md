# Introduction

**Pretend** is a Laravel package for **user impersonation** built on top of Laravel Sanctum.  
It allows one authenticated user (the **impersonator**) to generate temporary tokens and act as another user (the **impersonated**).

**Potential users?**
- Applications where admins need to log in as a user for troubleshooting.
- Multi-tenant systems where support staff or developers may need to replicate a user’s session.
- Any application that requires controlled, auditable impersonation of users.
