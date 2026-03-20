defmodule PhoenixApi.RateLimiterTest do
  use ExUnit.Case, async: true

  alias PhoenixApi.RateLimiter

  setup do
    {:ok, server} =
      start_supervised({RateLimiter,
       [
         name: nil,
         per_user_limit: 3,
         user_window_ms: 60_000,
         global_limit: 5,
         global_window_ms: 60_000
       ]})

    {:ok, server: server}
  end

  test "allows requests under the per-user limit", %{server: s} do
    assert RateLimiter.check(1, s) == :ok
    assert RateLimiter.check(1, s) == :ok
    assert RateLimiter.check(1, s) == :ok
  end

  test "blocks the request that exceeds per-user limit", %{server: s} do
    for _ <- 1..3, do: RateLimiter.check(1, s)
    assert RateLimiter.check(1, s) == {:error, :rate_limited}
  end

  test "different users have independent counters", %{server: s} do
    for _ <- 1..3, do: RateLimiter.check(1, s)
    assert RateLimiter.check(2, s) == :ok
  end

  test "blocks all users when global limit is reached", %{server: s} do
    for i <- 1..5, do: assert RateLimiter.check(i, s) == :ok
    assert RateLimiter.check(99, s) == {:error, :rate_limited}
  end

  test "reset clears all counters", %{server: s} do
    for _ <- 1..3, do: RateLimiter.check(1, s)
    assert RateLimiter.check(1, s) == {:error, :rate_limited}
    RateLimiter.reset(s)
    assert RateLimiter.check(1, s) == :ok
  end
end
