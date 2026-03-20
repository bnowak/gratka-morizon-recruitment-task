defmodule PhoenixApi.RateLimiter do
  @moduledoc """
  GenServer implementing a fixed-window rate limiter using ETS.
  Limits are configurable so tests can use small values.
  The `name: nil` option lets tests start anonymous instances (no name registration).
  """
  use GenServer

  # --- Public API ---

  def start_link(opts \\ []) do
    name = Keyword.get(opts, :name, __MODULE__)

    if name do
      GenServer.start_link(__MODULE__, opts, name: name)
    else
      GenServer.start_link(__MODULE__, opts)
    end
  end

  @doc "Returns :ok or {:error, :rate_limited}. `server` defaults to the production singleton."
  def check(user_id, server \\ __MODULE__) do
    GenServer.call(server, {:check, user_id})
  end

  @doc "Clears all counters. Used in tests to reset state between test cases."
  def reset(server \\ __MODULE__) do
    GenServer.call(server, :reset)
  end

  # --- GenServer callbacks ---

  def init(opts) do
    cfg = Application.get_env(:phoenix_api, :rate_limiter, [])

    state = %{
      table: :ets.new(:rate_limiter, [:set, :protected]),
      per_user_limit: Keyword.get(opts, :per_user_limit, Keyword.get(cfg, :per_user_limit, 5)),
      user_window_ms:
        Keyword.get(opts, :user_window_ms, Keyword.get(cfg, :user_window_ms, 10 * 60 * 1000)),
      global_limit: Keyword.get(opts, :global_limit, Keyword.get(cfg, :global_limit, 1000)),
      global_window_ms:
        Keyword.get(
          opts,
          :global_window_ms,
          Keyword.get(cfg, :global_window_ms, 60 * 60 * 1000)
        )
    }

    {:ok, state}
  end

  def handle_call({:check, user_id}, _from, state) do
    now = System.monotonic_time(:millisecond)

    result =
      with :ok <-
             do_check(state.table, :global, now, state.global_window_ms, state.global_limit),
           :ok <-
             do_check(
               state.table,
               {:user, user_id},
               now,
               state.user_window_ms,
               state.per_user_limit
             ) do
        :ok
      end

    {:reply, result, state}
  end

  def handle_call(:reset, _from, state) do
    :ets.delete_all_objects(state.table)
    {:reply, :ok, state}
  end

  # --- Private ---

  defp do_check(table, key_prefix, now, window_ms, limit) do
    window_id = div(now, window_ms)
    ets_key = {key_prefix, window_id}

    count = :ets.update_counter(table, ets_key, {2, 1}, {ets_key, 0})

    if count <= limit, do: :ok, else: {:error, :rate_limited}
  end
end
